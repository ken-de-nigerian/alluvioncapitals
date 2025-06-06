<?php

namespace App\Http\Controllers\Campaigns;

use App\Models\Campaign;
use App\Models\Category;
use App\Services\CampaignFilterService;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;

class CampaignCategoriesController extends BaseCampaignController
{
    /**
     * @return Response
     */
    public function categories()
    {
        return Inertia::render('Categories/Index', [
            'categories' => Inertia::defer(fn () => $this->getCategories()),
            'campaigns' => Campaign::with(['category' => function($query) {
                $query->select('id', 'name', 'slug');
            }])
                ->select([
                    'id',
                    'title',
                    'slug',
                    'goal',
                    'featured',
                    'campaign_images',
                    'campaign_video',
                    'funds_raised',
                    'expires_at',
                    'category_id'
                ])
                ->inRandomOrder()
                ->where('status', 'active')
                ->where('is_complete', '!=', 'yes')
                ->where('expires_at', '>', now())
                ->orderBy('featured', 'DESC')
                ->latest()
                ->take(10)
                ->get()
        ]);
    }

    /**
     * @param string $slug
     * @return Response
     */
    public function showCategories(string $slug)
    {
        $category = Category::where('slug', $slug)
            ->where('status', 'active')
            ->select('id', 'name', 'slug', 'image')
            ->firstOrFail();

        return Inertia::render('Categories/Show', [
            'campaigns' => Inertia::defer(fn () => $this->getCampaigns($slug)),
            'category' => $category,
        ]);
    }

    /**
     * @return AbstractPaginator|LengthAwarePaginator
     */
    protected function getCampaigns(string $slug)
    {
        sleep(1);
        $query = Campaign::with(['category' => function($query) {
            $query->select('id', 'name', 'slug');
        }])
        ->whereHas('category', function($query) use ($slug) {
            $query->where('slug', $slug);
        })
        ->select([
            'id',
            'title',
            'slug',
            'goal',
            'featured',
            'campaign_images',
            'campaign_video',
            'funds_raised',
            'expires_at',
            'category_id'
        ])
        ->where('status', 'active');

        return (new CampaignFilterService())->applyFilters($query, request());
    }

    /**
     * @return mixed
     */
    protected function getCategories()
    {
        sleep(1);
        return Category::where('status', 'active')
            ->select('id', 'name', 'slug', 'image')
            ->orderBy('name')
            ->get();
    }
}
