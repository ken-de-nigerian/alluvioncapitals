<?php

namespace App\Http\Controllers\Campaigns;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CampaignSearchController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $query = $request->input('query');
        $campaigns = collect();

        if (!empty($query) && strlen($query) >= 2) {
            $campaigns = Campaign::with(['user', 'category'])
                ->where('status', 'active')
                ->where('title', 'like', "%$query%")
                ->latest()
                ->get()
                ->map(fn ($campaign) => $this->formatCampaign($campaign));
        }

        return response()->json($campaigns);
    }

    /**
     * @param Campaign $campaign
     * @return array
     */
    private function formatCampaign(Campaign $campaign): array
    {
        return [
            'id' => $campaign->id,
            'title' => $campaign->title,
            'first_image' => $campaign->first_image,
            'funds_raised' => $campaign->funds_raised,
            'goal' => $campaign->goal,
            'progress' => $campaign->progress,
            'created_at' => $campaign->created_at,
            'first_name' => $campaign->first_name,
            'last_name' => $campaign->last_name,
            'days_left_text' => $campaign->days_left_text,
            'status_badge' => $campaign->status_badge,
            'show_route' => route('campaigns.show', $campaign->slug),
        ];
    }
}
