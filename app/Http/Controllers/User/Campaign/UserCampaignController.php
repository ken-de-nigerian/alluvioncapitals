<?php

namespace App\Http\Controllers\User\Campaign;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Throwable;

class UserCampaignController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get counts - optimized with single queries for each count
        $activeCampaigns = Campaign::where('status', 'active')
            ->where('user_id', auth()->id())
            ->count();
        $inactiveCampaigns = Campaign::onlyTrashed()
            ->where('user_id', auth()->id())
            ->where('status', 'inactive')
            ->count();

        return Inertia::render('User/Campaign/Index', [
            'campaigns' => Inertia::defer(fn () => $this->getActiveCampaigns()),
            'activeCampaigns' => $activeCampaigns,
            'inactiveCampaigns' => $inactiveCampaigns,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function archived(Request $request)
    {
        // Get counts - optimized with single queries for each count
        $activeCampaigns = Campaign::where('status', 'active')
            ->where('user_id', auth()->id())
            ->count();
        $inactiveCampaigns = Campaign::onlyTrashed()
            ->where('user_id', auth()->id())
            ->where('status', 'inactive')
            ->count();

        return Inertia::render('User/Campaign/Archived', [
            'campaigns' => Inertia::defer(fn () => $this->getArchivedCampaigns()),
            'activeCampaigns' => $activeCampaigns,
            'inactiveCampaigns' => $inactiveCampaigns,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'exists:campaigns,id']
        ], [
            'ids.required' => 'Please select at least one campaign to mark as complete.',
            'ids.min' => 'Please select at least one campaign to mark as complete.',
            'ids.exists' => 'One or more selected campaigns are already completed or no longer exist.'
        ]);

        try {
            // Count active campaigns before archiving for an accurate success message
            $count = Campaign::whereIn('id', $validated['ids'])
                ->count();

            // Use transaction to ensure all updates succeed or fail together
            DB::transaction(function () use ($validated) {
                Campaign::whereIn('id', $validated['ids'])
                    ->update([
                        'is_complete' => 'yes',
                        'expires_at' => now()->subDay()
                    ]);
            });

            return redirect()->back()->with('success', $count . ' ' . str('campaign')->plural($count) . ' completed successfully.');

        } catch (Exception $e) {
            Log::error('Failed to complete campaigns: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to complete campaigns. Please try again later.');
        } catch (Throwable $e) {
            return redirect()->back()->with('error', 'Failed to complete campaigns: ' . $e->getMessage());
        }
    }

    /**
     * Archive the specified resource from storage.
     */
    public function archive(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'exists:campaigns,id,deleted_at,NULL']
        ], [
            'ids.required' => 'Please select at least one campaign to archive.',
            'ids.min' => 'Please select at least one campaign to archive.',
            'ids.exists' => 'One or more selected campaigns are already archived or no longer exist.'
        ]);

        try {
            // Count active campaigns before archiving for an accurate success message
            $count = Campaign::whereIn('id', $validated['ids'])
                ->whereNull('deleted_at')
                ->count();

            // Use transaction to ensure all updates succeed or fail together
            DB::transaction(function () use ($validated) {
                Campaign::whereIn('id', $validated['ids'])
                    ->whereNull('deleted_at')
                    ->update([
                        'status' => 'inactive',
                        'deleted_at' => now()
                    ]);
            });

            return redirect()->back()->with('success', $count . ' ' . str('campaign')->plural($count) . ' archived successfully.');

        } catch (Exception $e) {
            Log::error('Failed to archive campaigns: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to archive campaigns. Please try again later.');
        } catch (Throwable $e) {
            return redirect()->back()->with('error', 'Failed to archive campaigns: ' . $e->getMessage());
        }
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'exists:campaigns,id,deleted_at,NOT_NULL']
        ], [
            'ids.required' => 'Please select at least one campaign to restore.',
            'ids.min' => 'Please select at least one campaign to restore.',
            'ids.exists' => 'One or more selected campaigns are not in the archive or no longer exist.'
        ]);

        try {
            // Count before restoration for an accurate success message
            $count = Campaign::withTrashed()
                ->whereIn('id', $validated['ids'])
                ->whereNotNull('deleted_at')
                ->count();

            // Use transaction to ensure all updates succeed or fail together
            DB::transaction(function () use ($validated) {
                // First, restore the records (which will set deleted_at to null)
                Campaign::withTrashed()
                    ->whereIn('id', $validated['ids'])
                    ->whereNotNull('deleted_at')
                    ->restore();

                // Then update their status to active
                Campaign::whereIn('id', $validated['ids'])
                    ->update(['status' => 'active']);
            });

            return redirect()->back()->with('success', $count . ' ' . str('campaign')->plural($count) . ' restored successfully.');

        } catch (Exception $e) {
            Log::error('Failed to restore campaigns: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to restore campaigns. Please try again later.');
        } catch (Throwable $e) {
            return redirect()->back()->with('error', 'Failed to restore campaigns: ' . $e->getMessage());
        }
    }

    /**
     * Permanently delete the specified resources from storage.
     * This will delete both trashed and non-trashed campaigns.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'exists:campaigns,id']
        ], [
            'ids.required' => 'Please select at least one campaign to permanently delete.',
            'ids.min' => 'Please select at least one campaign to permanently delete.',
            'ids.exists' => 'One or more selected campaigns no longer exist.'
        ]);

        try {
            // Count before deletion for an accurate success message
            $count = Campaign::whereIn('id', $validated['ids'])
                ->count();

            // Use transaction to ensure all deletions succeed or fail together
            DB::transaction(function () use ($validated) {
                // Force delete both trashed and non-trashed campaigns
                Campaign::whereIn('id', $validated['ids'])
                    ->forceDelete();
            });

            return redirect()->back()->with('success', $count . ' ' . str('campaign')->plural($count) . ' deleted successfully.');

        } catch (Exception $e) {
            Log::error('Failed to permanently delete campaigns: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to permanently delete campaigns. Please try again later.');
        } catch (Throwable $e) {
            return redirect()->back()->with('error', 'Failed to permanently delete campaigns: ' . $e->getMessage());
        }
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
     * Get active campaigns with search and pagination for the authenticated user.
     */
    private function getActiveCampaigns()
    {
        sleep(1);
        $request = request();

        $query = Campaign::where('user_id', auth()->id())
        ->where('status', 'active');

        // Apply search filter if a search term is provided
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $campaigns = $query->latest()
            ->paginate(8)
            ->withQueryString();

        // Transform the campaign collection
        $campaigns->getCollection()->transform(function ($campaign) {
            return $this->cleanCampaign($campaign);
        });

        return $campaigns;
    }

    /**
     * Get archived campaigns with search and pagination.
     */
    private function getArchivedCampaigns()
    {
        sleep(1);
        $request = request(); // Access the current request

        $campaigns = Campaign::onlyTrashed()
            ->where('user_id', auth()->id())
            ->where('status', 'inactive')
            ->when($request->search, function($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%$search%");
                });
            })
            ->latest()
            ->paginate(8)
            ->withQueryString();

        $campaigns->getCollection()->transform(fn($campaign) => $this->cleanCampaign($campaign));

        return $campaigns;
    }
}
