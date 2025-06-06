<?php

namespace App\Http\Controllers\Campaigns;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Category;
use Auth;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Intervention\Image\Laravel\Facades\Image;

class EditCampaignController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     */
    public function editDetails(Campaign $campaign, Request $request)
    {
        $categories = Category::select(['id', 'name'])
            ->orderBy('name')
            ->get();

        // Get session data if exists
        $sessionDetails = $request->session()->get('campaign.edit.details', []);

        return Inertia::render('Campaign/Edit/EditCampaignDetails', [
            'categories' => $categories,
            'campaign' => $campaign->only([
                'id',
                'category_id',
                'title',
                'goal',
                'expires_at',
                'summary'
            ]),
            'old' => $sessionDetails
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeDetails(Request $request, Campaign $campaign): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'goal' => 'required|numeric|min:1',
            'expires_at' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    try {
                        $date = Carbon::parse($value);
                        if ($date->isPast()) {
                            $fail('The expiration date must be in the future.');
                        }
                    } catch (Exception $e) {
                        $fail('The expiration date is not a valid date: ' . $e->getMessage());
                    }
                },
            ],
            'summary' => 'required',
        ]);

        // Store the validated data in session
        $request->session()->put('campaign.edit.details', [
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']),
            'category_id' => $validated['category_id'],
            'goal' => $validated['goal'],
            'expires_at' => Carbon::parse($validated['expires_at'])->format('Y-m-d'),
            'summary' => $validated['summary'],
        ]);

        return redirect()->route('campaigns.edit.contact.info', $campaign->id);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function editContactInfo(Campaign $campaign, Request $request)
    {
        // Check if campaign details exist in session
        if (!$request->session()->has('campaign.edit.details')) {
            return redirect()
                ->route('campaigns.edit.details', $campaign->id)
                ->with('error', 'Please complete campaign details first');
        }

        // Get session data if exists
        $sessionDetails = $request->session()->get('campaign.edit.contact-info', []);

        return Inertia::render('Campaign/Edit/EditCampaignContactInfo', [
            'campaign' => $campaign->only([
                'id',
                'first_name',
                'last_name',
                'email',
                'phone_number',
                'country_id',
                'state_id',
                'city',
                'address'
            ]),
            'old' => $sessionDetails
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeContactInfo(Request $request, Campaign $campaign): RedirectResponse
    {
        // Check if campaign details exist in session
        if (!$request->session()->has('campaign.edit.details')) {
            return redirect()
                ->route('campaigns.edit.details', $campaign->id)
                ->with('error', 'Please complete campaign details first');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'phone_number' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'country_id' => 'required',
            'state' => 'required|string|max:255',
            'state_id' => 'required',
            'city' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);

        // Store the validated data in session
        $request->session()->put('campaign.edit.contact-info', [
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
            'country' => $validated['country'],
            'country_id' => $validated['country_id'],
            'state' => $validated['state'],
            'state_id' => $validated['state_id'],
            'city' => $validated['city'],
            'address' => $validated['address'],
        ]);

        // Redirect to the next step with the campaign ID
        return redirect()->route('campaigns.edit.photos.videos', $campaign->id);
    }

    /**
     * Show the form for adding photos and videos.
     */
    public function editPhotosAndVideos(Request $request, Campaign $campaign)
    {
        // Check if previous steps are completed
        if (!$request->session()->has('campaign.edit.details') ||
            !$request->session()->has('campaign.edit.contact-info')) {
            return redirect()
                ->route('campaigns.edit.details', $campaign->id)
                ->with('error', 'Please complete previous steps first');
        }

        // Get session data
        $sessionDetails = $request->session()->get('campaign.edit.media', []);

        return Inertia::render('Campaign/Edit/EditCampaignPhotosAndVideos', [
            'campaign' => $campaign->only([
                'id',
                'campaign_images',
                'campaign_video',
            ]),
            'media' => $sessionDetails
        ]);
    }

    /**
     * Store photos and videos in session.
     */
    public function storePhotosAndVideos(Request $request, Campaign $campaign): RedirectResponse
    {
        // Validate previous steps
        if (!$request->session()->has('campaign.edit.details') ||
            !$request->session()->has('campaign.edit.contact-info')) {
            return redirect()
                ->route('campaigns.edit.details', $campaign->id)
                ->with('error', 'Please complete previous steps first');
        }

        $validated = $request->validate([
            'photos' => 'required_without:existing_photos|array|min:1',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:8192',
            'existing_photos' => 'sometimes|array',
            'existing_photos.*' => 'sometimes|string',
            'campaign_video' => 'nullable|url',
        ]);

        // Handle file uploads
        $photos = [];

        // Store new photos with full URLs
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $storedPath = $photo->store('temp/campaigns', 'public');
                $photos[] = Storage::disk('public')->url($storedPath);
            }
        }

        // Keep existing photos (assuming these are already full URLs)
        if ($request->has('existing_photos')) {
            $photos = array_merge($photos, $request->input('existing_photos'));
        }

        // Store media data in session
        $request->session()->put('campaign.edit.media', [
            'campaign_images' => $photos,
            'campaign_video' => $validated['campaign_video'] ?? null,
        ]);

        return redirect()->route('campaigns.edit.supporting.documents', $campaign->id);
    }

    /**
     * Show the form for adding supporting documents.
     */
    public function editSupportingDocuments(Request $request, Campaign $campaign)
    {
        // Ensure previous steps are completed
        if (!$request->session()->has('campaign.edit.details') ||
            !$request->session()->has('campaign.edit.contact-info') ||
            !$request->session()->has('campaign.edit.media')) {
            return redirect()
                ->route('campaigns.edit.details', $campaign->id)
                ->with('error', 'Please complete previous steps first');
        }

        // Get session data
        $sessionDetails = $request->session()->get('campaign.edit.documents', []);

        return Inertia::render('Campaign/Edit/EditCampaignSupportingDocuments', [
            'campaign' => $campaign->only([
                'id',
                'supporting_documents',
            ]),
            'documents' => $sessionDetails
        ]);
    }

    /**
     * Store supporting documents (images and files) in session.
     */
    public function storeSupportingDocuments(Request $request, Campaign $campaign): RedirectResponse
    {
        // Check if previous steps are completed
        if (!$request->session()->has('campaign.edit.details') ||
            !$request->session()->has('campaign.edit.contact-info') ||
            !$request->session()->has('campaign.edit.media')) {
            return redirect()
                ->route('campaigns.edit.details', $campaign->id)
                ->with('error', 'Please complete previous steps first');
        }

        // Validate input
        $request->validate([
            'documents' => 'required_without:existing_documents|array|min:1',
            'documents.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx|max:5120',
            'existing_documents' => 'sometimes|array',
            'existing_documents.*' => 'string'
        ]);

        // Get existing documents from input or default to empty
        $documents = [];

        // Handle new uploads
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $storedPath = $file->store('temp/documents', 'public');
                $documents[] = Storage::disk('public')->url($storedPath);
            }
        }

        // Keep existing documents (assuming these are already full URLs)
        if ($request->has('existing_documents')) {
            $documents = array_merge($documents, $request->input('existing_documents'));
        }

        // Store in session
        $request->session()->put('campaign.edit.documents', [
            'supporting_documents' => $documents
        ]);

        return redirect()->route('campaigns.edit.publish', $campaign->id);
    }

    /**
     * Show the final form to create the campaign from session data.
     */
    public function editCampaigns(Request $request, Campaign $campaign)
    {
        // Check if previous steps are completed
        if (!$request->session()->has('campaign.edit.details') ||
            !$request->session()->has('campaign.edit.contact-info') ||
            !$request->session()->has('campaign.edit.media') ||
            !$request->session()->has('campaign.edit.documents')) {
            return redirect()
                ->route('campaigns.edit.details', $campaign->id)
                ->with('error', 'Please complete previous steps first');
        }

        return Inertia::render('Campaign/Edit/PublishCampaigns', [
            'campaign' => $campaign->only([
                'id',
                'has_ad_promotion'
            ])
        ]);
    }

    /**
     * Final method to update the campaign from session data with proper image handling
     */
    public function storeEditedCampaigns(Request $request, Campaign $campaign): RedirectResponse
    {
        try {

            $request->validate([
                'selected_plan' => 'required|json',
                'selected_addons' => 'nullable|json'
            ]);

            // Get all session data
            $details = $request->session()->get('campaign.edit.details');
            $contactInfo = $request->session()->get('campaign.edit.contact-info');
            $media = $request->session()->get('campaign.edit.media', []);
            $documents = $request->session()->get('campaign.edit.documents', []);

            // Extract file URLs from the session
            $campaignImageUrls = $media['campaign_images'] ?? [];
            $supportingDocumentUrls = $documents['supporting_documents'] ?? [];

            // Extract promotion details
            $hasAdPromotion = [
                'selected_plan' => json_decode($request->input('selected_plan', '{}'), true),
                'selected_addons' => json_decode($request->input('selected_addons', '[]'), true)
            ];

            // Ensure directories exist
            Storage::disk('public')->makeDirectory('campaign-images');
            Storage::disk('public')->makeDirectory('supporting-documents');

            // Process and move campaign images with resizing
            $finalImageUrls = [];
            foreach ($campaignImageUrls as $url) {
                // Extract relative path from URL
                $path = parse_url($url, PHP_URL_PATH);
                $path = str_replace('/storage/', '', $path);

                if (Storage::disk('public')->exists($path)) {
                    $filename = basename($path);
                    $newPath = 'campaign-images/' . $filename;

                    // Read and resize the image
                    $image = Image::read(Storage::disk('public')->get($path));
                    $image->resize(1120, 630);
                    Storage::disk('public')->put($newPath, $image->encode());

                    // Store only the final public URL
                    $finalImageUrls[] = asset('storage/' . $newPath);
                }
            }

            // Move supporting documents
            $finalDocumentUrls = [];
            foreach ($supportingDocumentUrls as $url) {
                // Extract relative path from URL
                $path = parse_url($url, PHP_URL_PATH);
                $path = str_replace('/storage/', '', $path);

                if (Storage::disk('public')->exists($path)) {
                    $filename = basename($path);
                    $newPath = 'supporting-documents/' . $filename;
                    Storage::disk('public')->move($path, $newPath);

                    // Store only the final public URL
                    $finalDocumentUrls[] = asset('storage/' . $newPath);
                }
            }

            // Update campaign
            $campaign->update(array_merge(
                $details,
                $contactInfo,
                [
                    'campaign_images' => json_encode($finalImageUrls),
                    'campaign_video' => $media['campaign_video'] ?? null,
                    'supporting_documents' => json_encode($finalDocumentUrls),
                    'has_ad_promotion' => json_encode($hasAdPromotion),
                    'user_id' => Auth::user()->id
                ]
            ));

            // Clear session data
            $request->session()->forget([
                'campaign.edit.details',
                'campaign.edit.contact-info',
                'campaign.edit.media',
                'campaign.edit.documents',
            ]);

            if (Auth::user()->role == 'admin'){
                return redirect()
                    ->route('admin.campaigns.index')
                    ->with('success', 'Campaign updated successfully!');
            }else{
                return redirect()
                    ->route('user.campaigns.index')
                    ->with('success', 'Campaign updated successfully!');
            }
        } catch (Exception $e) {
            Log::error('Campaign update failed: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Failed to update campaign. Please try again.');
        }
    }
}
