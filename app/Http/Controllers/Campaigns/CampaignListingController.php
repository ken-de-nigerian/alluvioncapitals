<?php

namespace App\Http\Controllers\Campaigns;

use App\Models\Campaign;
use App\Services\CampaignFilterService;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;

class CampaignListingController extends BaseCampaignController
{
    /**
     * @return Response
     */
    public function index()
    {
        return Inertia::render('Campaign/Index', [
            'campaigns' => Inertia::defer(fn () => $this->getCampaigns()),
            'categories' => $this->getCategories(),
            'filters' => $this->getFilters(request()),
            'min_goal' => $this->getMinMaxGoals()['min'],
            'max_goal' => $this->getMinMaxGoals()['max'],
        ]);
    }

    /**
     * @return AbstractPaginator|LengthAwarePaginator
     */
    protected function getCampaigns()
    {
        sleep(1);
        $query = Campaign::with(['category' => function($query) {
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
            ->where('status', 'active');
        return (new CampaignFilterService())->applyFilters($query, request());
    }
}
