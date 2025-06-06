<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Inertia\Inertia;
use Inertia\Response;

class WidgetController extends Controller
{
    /**
     * @param string $slug
     * @return Response
     */
    public function widgetsLarge(string $slug)
    {
        // Show Campaign Details
        $campaign = Campaign::with('category')
            ->where('slug', $slug)
            ->select([
                'id',
                'title',
                'slug',
                'goal',
                'first_name',
                'last_name',
                'email',
                'phone_number',
                'country',
                'featured',
                'campaign_images',
                'campaign_video',
                'funds_raised',
                'expires_at',
                'category_id',
                'created_at'
            ])
            ->firstOrFail();

        return Inertia::render('Campaign/Widgets/Large', [
            'campaign' => $campaign
        ]);
    }

    /**
     * @param string $slug
     * @return Response
     */
    public function widgetsSmall(string $slug)
    {
        // Show Campaign Details
        $campaign = Campaign::with('category')
            ->where('slug', $slug)
            ->select([
                'id',
                'title',
                'slug',
                'goal',
                'first_name',
                'last_name',
                'email',
                'phone_number',
                'country',
                'featured',
                'campaign_images',
                'campaign_video',
                'funds_raised',
                'expires_at',
                'category_id',
                'created_at'
            ])
            ->firstOrFail();

        return Inertia::render('Campaign/Widgets/Small', [
            'campaign' => $campaign
        ]);
    }
}
