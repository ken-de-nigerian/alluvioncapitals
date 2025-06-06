<?php

namespace App\Services;

use App\Models\Campaign;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\LengthAwarePaginator;

class CampaignFilterService
{
    /**
     * @param Builder $query
     * @param Request $request
     * @return AbstractPaginator|LengthAwarePaginator
     */
    public function applyFilters(Builder $query, Request $request): LengthAwarePaginator|AbstractPaginator
    {
        $this->applyTabFilter($query, $request);
        $this->applyGoalFilters($query, $request);
        $this->applyDeadlineFilter($query, $request);
        $this->applyCategoryFilter($query, $request);

        return $query->paginate(12)->withQueryString();
    }

    /**
     * @param Builder $query
     * @param Request $request
     * @return void
     */
    private function applyTabFilter(Builder $query, Request $request): void
    {
        $today = today()->addDay();

        switch ($request->get('tab', 'all')) {
            case 'latest':
                $query->where('is_complete', '!=', 'yes')
                    ->where(function ($q) use ($today) {
                        $q->where('expires_at', '>', $today)
                            ->orWhereNull('expires_at');
                    });
                break;

            case 'featured':
                $query->where('featured', 'yes')
                    ->where('is_complete', '!=', 'yes')
                    ->where(function ($q) use ($today) {
                        $q->where('expires_at', '>', $today)
                            ->orWhereNull('expires_at');
                    });
                break;

            case 'popular':
                $query->whereHas('donations', function ($q) {
                    $q->where('status', 'approved')
                        ->groupBy('campaign_id')
                        ->havingRaw('COUNT(*) > 5');
                })
                    ->withCount(['donations' => function ($q) {
                        $q->where('status', 'approved');
                    }])
                    ->where('is_complete', '!=', 'yes')
                    ->where(function ($q) use ($today) {
                        $q->where('expires_at', '>', $today)
                            ->orWhereNull('expires_at');
                    })
                    ->orderBy('donations_count', 'DESC');
                break;

            case 'ended':
                $query->where(function ($q) use ($today) {
                    $q->where('expires_at', '<', $today)
                        ->orWhere('is_complete', 'yes');
                });
                break;

            default:
                // No tab filtering, but still order active > expired
                break;
        }

        // Global ordering: Active campaigns first, then expired ones
        $query->orderByRaw("CASE
        WHEN expires_at IS NULL THEN 0
        WHEN expires_at > ? THEN 0
        ELSE 1 END", [$today])
            ->orderByDesc('featured')
            ->latest();
    }

    /**
     * @param Builder $query
     * @param Request $request
     * @return void
     */
    private function applyGoalFilters(Builder $query, Request $request): void
    {
        $minGoal = Campaign::where('status', 'active')->min('goal') ?? 0;
        $maxGoal = Campaign::where('status', 'active')->max('goal') ?? 100000;

        if ($request->filled('goal_min') && is_numeric($request->goal_min)) {
            $query->where('goal', '>=', max($request->goal_min, $minGoal));
        }

        if ($request->filled('goal_max') && is_numeric($request->goal_max)) {
            $query->where('goal', '<=', min($request->goal_max, $maxGoal));
        }
    }

    /**
     * @param Builder $query
     * @param Request $request
     * @return void
     */
    private function applyDeadlineFilter(Builder $query, Request $request): void
    {
        if ($request->filled('deadline')) {
            try {
                $deadline = Carbon::parse($request->deadline);
                $query->whereDate('expires_at', '<=', $deadline);
            } catch (Exception $e) {
                report($e);
            }
        }
    }

    /**
     * @param Builder $query
     * @param Request $request
     * @return void
     */
    private function applyCategoryFilter(Builder $query, Request $request): void
    {
        if ($request->filled('category')) {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }
    }
}
