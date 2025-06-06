<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Campaigns\BaseCampaignController;
use App\Models\Campaign;
use App\Models\Category;
use Inertia\Inertia;

class HomeController extends BaseCampaignController
{
    public function index()
    {
        return Inertia::render('Homepage/Index', [
            'categories' => Inertia::defer(fn () => $this->getCategories()),
            'top_fundraising_causes' => $this->topFundraisingCause(),
            'latest_campaigns' => $this->getLatestCampaigns()
        ]);
    }

    /**
     * @return mixed
     */
    protected function getCategories()
    {
        sleep(1);
        return Category::where('status', 'active')
            ->select('id', 'name', 'slug', 'image')
            ->inRandomOrder()
            ->orderBy('name')
            ->take('6')
            ->get();
    }

    protected function topFundraisingCause()
    {
        return Category::where('status', 'active')
            ->select('id', 'name', 'slug')
            ->withSum('campaigns as total_funds_raised', 'funds_raised')
            ->orderByDesc('total_funds_raised')
            ->take('6')
            ->get();
    }

    protected function getLatestCampaigns()
    {
        $today = today()->addDay();
        return Campaign::with(['category' => function($query) {
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
            ->where('is_complete', 'no')
            ->where(function ($q) use ($today) {
                $q->where('expires_at', '>', $today)
                    ->orWhereNull('expires_at');
            })
            ->orderBy('created_at', 'desc')
            ->take('6')
            ->get();
    }
}
