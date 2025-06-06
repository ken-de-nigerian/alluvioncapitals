<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Intervention\Image\Laravel\Facades\Image;
use Throwable;

class CategoryController extends Controller
{
    /**
     * Display a listing of the published categories.
     */
    public function index()
    {
        $query = Category::query()
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->where('status', 'active')
            ->withCount([
                'campaigns' => function ($query) {
                    $query->withTrashed();
                }
            ])
            ->orderBy('name');

        $categories = $query->paginate(8)->withQueryString();

        // Exclude "updated_at" and "deleted_at" from each item
        $categories->getCollection()->transform(function ($category) {
            return collect($category->toArray())->except([
                'updated_at',
                'deleted_at'
            ]);
        });

        return Inertia::render('Admin/Category/Index', [
            'categories' => $categories,
            'activeCategories' => Category::where('status', 'active')->count(),
            'inactiveCategories' => Category::onlyTrashed()
                ->where('status', 'inactive')
                ->count(),
            'filters' => request()->only('search')
        ]);
    }

    /**
     * Display a listing of the archived categories.
     */
    public function archived()
    {
        $query = Category::query()
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->onlyTrashed()
            ->where('status', 'inactive')
            ->withCount('campaigns')
            ->orderBy('name');

        return Inertia::render('Admin/Category/Archived', [
            'categories' => $query->paginate(8)->withQueryString(),
            'activeCategories' => Category::where('status', 'active')->count(),
            'inactiveCategories' => Category::onlyTrashed()
                ->where('status', 'inactive')
                ->count(),
            'filters' => request()->only('search')
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Category/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_name' => 'required|unique:categories,name',
            'category_status' => 'required',
            'category_image' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg',
                'max:2048',
            ],
        ], [
            'category_name.required' => 'Category name is required.',
            'category_name.unique' => 'Category name already exists.',
            'category_status.required' => 'Category status is required.',
            'category_image.required' => 'Please select an image to upload.',
            'category_image.image' => 'The file must be an image.',
            'category_image.mimes' => 'The image must be a PNG, JPG, or JPEG file.',
            'category_image.max' => 'The image must not exceed 2MB in size.',
        ]);

        try {

            // Storage path
            $storagePath = 'category-images/';

            // Handle image upload
            $image = $request->file('category_image');
            $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
            $fullPath = $storagePath . $filename;

            // Resize and save
            $resizedImage = Image::read($image)->resize(540, 360);
            Storage::disk('public')->put($fullPath, $resizedImage->encode());

            // Create category
            Category::create([
                'name' => $validated['category_name'],
                'slug' => Str::slug($validated['category_name']),
                'status' => $validated['category_status'],
                'image' => Storage::disk('public')->url($fullPath)
            ]);

            return redirect()->back()->with('success', 'Category created successfully.');
        } catch (Exception) {
            if (isset($filename)) {
                Storage::disk('public')->delete('category-images/' . $filename);
            }

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Something went wrong. Please try again.']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $slug, Request $request)
    {
        $category = $this->getCategoryWithCounts($slug);

        return Inertia::render('Admin/Category/Show/Published', [
            'category' => $this->cleanCategory($category),
            'campaigns' => Inertia::defer(fn () => $this->getActiveCampaigns($category, $request)),
            'activeCampaigns' => $category->published_count,
            'inactiveCampaigns' => $category->archived_count,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Display the specified archived resource.
     */
    public function showArchived(string $slug, Request $request)
    {
        $category = $this->getCategoryWithCounts($slug);

        return Inertia::render('Admin/Category/Show/Archived', [
            'category' => $this->cleanCategory($category),
            'campaigns' => Inertia::defer(fn () => $this->getArchivedCampaigns($category, $request)),
            'activeCampaigns' => $category->published_count,
            'inactiveCampaigns' => $category->archived_count,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        return Inertia::render('Admin/Category/Edit', [
            'category' => $category,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'category_name' => 'required|string|min:3|max:255|unique:categories,name,' . $category->id,
            'category_status' => 'required|in:active,inactive',
            'category_image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg',
                'max:2048',
            ],
        ], [
            'category_name.required' => 'Category name is required.',
            'category_name.unique' => 'Category name already exists.',
            'category_status.required' => 'Category status is required.',
            'category_image.image' => 'The file must be an image.',
            'category_image.mimes' => 'The image must be a PNG, JPG, or JPEG file.',
            'category_image.max' => 'The image must not exceed 2MB in size.',
        ]);

        try {

            $newImageUrl = null;

            if ($request->hasFile('category_image')) {
                // Delete an old image if it exists
                if ($category->image) {
                    $oldPath = str_replace(Storage::disk('public')->url(''), '', $category->image);
                    Storage::disk('public')->delete($oldPath);
                }

                $image = $request->file('category_image');
                $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
                $fullPath = 'category-images/' . $filename;

                // Resize and save image
                $resizedImage = Image::read($image)->resize(540, 360);
                Storage::disk('public')->put($fullPath, $resizedImage->encode());

                $newImageUrl = Storage::disk('public')->url($fullPath);
            }

            $category->update([
                'name' => $validated['category_name'],
                'slug' => Str::slug($validated['category_name']),
                'status' => $validated['category_status'],
                'image' => $newImageUrl ?? $category->image,
            ]);

            return redirect()->back()->with('success', 'Category updated successfully.');
        } catch (Exception) {
            // Clean up the uploaded image if something went wrong
            if (isset($fullPath)) {
                Storage::disk('public')->delete($fullPath);
            }

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Something went wrong. Please try again.']);
        }
    }

    /**
     * Archive the specified resource from storage.
     */
    public function archive(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'exists:categories,id,deleted_at,NULL']
        ], [
            'ids.required' => 'Please select at least one category to archive.',
            'ids.min' => 'Please select at least one category to archive.',
            'ids.exists' => 'One or more selected categories are already archived or no longer exist.'
        ]);

        try {
            // Count active categories before archiving for an accurate success message
            $count = Category::whereIn('id', $validated['ids'])
                ->whereNull('deleted_at')
                ->count();

            // Use transaction to ensure all updates succeed or fail together
            DB::transaction(function () use ($validated) {
                Category::whereIn('id', $validated['ids'])
                    ->whereNull('deleted_at')
                    ->update([
                        'status' => 'inactive',
                        'deleted_at' => now()
                    ]);
            });

            return redirect()->back()->with('success', $count . ' ' . str('category')->plural($count) . ' archived successfully.');

        } catch (Exception $e) {
            Log::error('Failed to archive categories: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to archive categories. Please try again later.');
        } catch (Throwable $e) {
            return redirect()->back()->with('error', 'Failed to archive categories: ' . $e->getMessage());
        }
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'exists:categories,id,deleted_at,NOT_NULL']
        ], [
            'ids.required' => 'Please select at least one category to restore.',
            'ids.min' => 'Please select at least one category to restore.',
            'ids.exists' => 'One or more selected categories are not in the archive or no longer exist.'
        ]);

        try {
            // Count before restoration for an accurate success message
            $count = Category::withTrashed()
                ->whereIn('id', $validated['ids'])
                ->whereNotNull('deleted_at')
                ->count();

            // Use transaction to ensure all updates succeed or fail together
            DB::transaction(function () use ($validated) {
                // First, restore the records (which will set deleted_at to null)
                Category::withTrashed()
                    ->whereIn('id', $validated['ids'])
                    ->whereNotNull('deleted_at')
                    ->restore();

                // Then update their status to active
                Category::whereIn('id', $validated['ids'])
                    ->update(['status' => 'active']);
            });

            return redirect()->back()->with('success', $count . ' ' . str('category')->plural($count) . ' restored successfully.');

        } catch (Exception $e) {
            Log::error('Failed to restore categories: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to restore categories. Please try again later.');
        } catch (Throwable $e) {
            return redirect()->back()->with('error', 'Failed to restore categories: ' . $e->getMessage());
        }
    }

    /**
     * Permanently delete the specified resources from storage.
     * This will delete both trashed and non-trashed categories.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'exists:categories,id']
        ], [
            'ids.required' => 'Please select at least one category to permanently delete.',
            'ids.min' => 'Please select at least one category to permanently delete.',
            'ids.exists' => 'One or more selected categories no longer exist.'
        ]);

        try {
            // Count before deletion for an accurate success message
            $count = Category::whereIn('id', $validated['ids'])
                ->count();

            // Use transaction to ensure all deletions succeed or fail together
            DB::transaction(function () use ($validated) {
                // Force delete both trashed and non-trashed categories
                Category::whereIn('id', $validated['ids'])
                    ->forceDelete();
            });

            return redirect()->back()->with('success', $count . ' ' . str('category')->plural($count) . ' deleted successfully.');

        } catch (Exception $e) {
            Log::error('Failed to permanently delete categories: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to permanently delete categories. Please try again later.');
        } catch (Throwable $e) {
            return redirect()->back()->with('error', 'Failed to permanently delete categories: ' . $e->getMessage());
        }
    }

    /**
     * Get active campaigns for a category.
     */
    private function getActiveCampaigns($category, Request $request)
    {
        sleep(1);
        return $category->campaigns()
            ->where('status', 'active')
            ->when($request->search, function($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%$search%");
                });
            })
            ->latest()
            ->paginate(8)
            ->withQueryString()
            ->through(fn($campaign) => $this->cleanCampaign($campaign));
    }

    /**
     * Get archived campaigns for a category.
     */
    private function getArchivedCampaigns($category, Request $request)
    {
        sleep(1);
        return $category->campaigns()
            ->onlyTrashed()
            ->where('status', 'inactive')
            ->when($request->search, function($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%$search%");
                });
            })
            ->latest()
            ->paginate(8)
            ->withQueryString()
            ->through(fn($campaign) => $this->cleanCampaign($campaign));
    }

    /**
     * Clean up a single campaign.
     */
    private function cleanCampaign($campaign)
    {
        return collect($campaign->toArray())->except([
            'user_id',
            'category_id',
            'first_name',
            'last_name',
            'email',
            'phone_number',
            'address',
            'city',
            'state',
            'country',
            'summary',
            'campaign_images',
            'campaign_video',
            'supporting_documents',
            'featured',
            'status',
            'is_complete',
            'expires_at',
            'updated_at',
            'deleted_at',
        ]);
    }

    /**
     * Clean up category data.
     */
    private function cleanCategory($category)
    {
        return collect($category->toArray())->except([
            'image',
            'created_at',
            'updated_at',
            'deleted_at',
            'name',
            'status',
            'published_count',
            'archived_count',
        ]);
    }

    /**
     * Get category with campaign counts.
     */
    private function getCategoryWithCounts(string $slug)
    {
        return Category::withCount([
            'campaigns as published_count' => function($query) {
                $query->where('status', 'active');
            },
            'campaigns as archived_count' => function($query) {
                $query->onlyTrashed()->where('status', 'inactive');
            }
        ])->where('slug', $slug)->firstOrFail();
    }
}
