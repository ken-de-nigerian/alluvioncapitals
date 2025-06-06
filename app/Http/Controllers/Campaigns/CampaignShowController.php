<?php

namespace App\Http\Controllers\Campaigns;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Inertia\Response;
use Inertia\ResponseFactory;

class CampaignShowController extends Controller
{
    /**
     * @param Request $request
     * @param string $slug
     * @return Response|ResponseFactory
     */
    public function showCampaigns(Request $request, string $slug)
    {
        // Clear session data
        $request->session()->forget([
            'campaign.donation.details',
        ]);

        // Show Campaign Details
        $campaign = Campaign::with('category')
            ->where('slug', $slug)
            ->withCount([
                'donations' => function ($query) {
                    $query->where('status', 'approved');
                },
                'comments' => function ($query) {
                    $query->where('status', 'active');
                }
            ])
            ->firstOrFail();

        // Get Donation Amounts
        $amounts = $campaign->getDonationAmounts();

        // Get Related Campaigns
        $relatedCampaigns = Campaign::with('category')
            ->where('category_id', $campaign->category->id)
            ->where('id', '!=', $campaign->id)
            ->limit(5)->get();

        // If not found
        if ($relatedCampaigns->isEmpty()) {
            $relatedCampaigns = Campaign::with('category')
                ->where('id', '!=', $campaign->id)
                ->inRandomOrder()
                ->limit(5)->get();
        }

        // Rewards associated with this campaign
        $rewards = $campaign->rewards()->latest()->get();

        // Donations associated with this campaign
        $donations = $campaign->donations()->where('status', 'approved')->latest()->paginate(8);

        // Comments & replies associated with this campaign
        $comments = $campaign->comments()
            ->where('status', 'active')
            ->latest()
            ->paginate(4);

        // Updates associated with this campaign
        $updates = $campaign->updates()->latest()->paginate(4);

        return Inertia('Campaign/Show', [
            'campaign' => $campaign,
            'amounts' => $amounts,
            'rewards' => $rewards,
            'donations' => $donations,
            'comments' => $comments,
            'updates' => $updates,
            'relatedCampaigns' => $relatedCampaigns,
        ]);
    }
}
