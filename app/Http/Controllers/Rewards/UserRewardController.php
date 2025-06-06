<?php

namespace App\Http\Controllers\Rewards;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Reward;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class UserRewardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Campaign $campaign)
    {
        // Rewards associated with the campaign
        $rewards = $campaign->rewards()->latest()->paginate(10);

        return Inertia::render('User/Rewards/Index', [
            'campaign' => $campaign,
            'rewards' => $rewards,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Campaign $campaign)
    {
        return Inertia::render('User/Rewards/Create', [
            'campaign' => $campaign
        ]);
    }

    /**
     * Store a newly created thank-you gift in storage.
     */
    public function store(Request $request, Campaign $campaign): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:1000'],
            'amount' => ['required', 'numeric', 'min:1', 'max:100000'],
            'physical_gift' => ['sometimes', 'boolean'],
        ], [
            'title.required' => 'Please give this thank-you gift a name',
            'amount.min' => 'The minimum donation amount must be at least ₦1',
            'description.max' => 'Please keep the description under 1000 characters',
        ]);

        try {
            Reward::create([
                'campaign_id' => $campaign->id,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'amount' => $validated['amount'],
                'requires_shipping' => $validated['physical_gift'] ?? false,
                'status' => 'active',
            ]);

            return redirect()
                ->route('user.campaigns.rewards.index', $campaign->id)
                ->with('success', 'Thank-you gift added successfully! Supporters will appreciate this.');

        } catch (Exception $exception) {
            Log::error('failed to add reward: ' . $exception->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $reward = Reward::with('campaign')->find($id);

        return Inertia::render('User/Rewards/Edit', [
            'reward' => $reward,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $reward = Reward::with('campaign')->findOrFail($id);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:1000'],
            'amount' => ['required', 'numeric', 'min:1', 'max:100000'],
            'physical_gift' => ['sometimes', 'boolean'],
            'status' => ['required'],
        ], [
            'title.required' => 'Please give this thank-you gift a name',
            'amount.min' => 'The minimum donation amount must be at least ₦1',
            'description.max' => 'Please keep the description under 1000 characters',
        ]);

        try {
            $reward->update([
                'campaign_id' => $reward->campaign->id,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'amount' => $validated['amount'],
                'requires_shipping' => $validated['physical_gift'] ?? false,
                'status' => $validated['status'],
            ]);

            return redirect()
                ->route('user.campaigns.rewards.index', $reward->campaign->id)
                ->with('success', 'Thank-you gift updated successfully! Supporters will appreciate this.');

        } catch (Exception $exception) {
            Log::error('Failed to update reward: ' . $exception->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Campaign $campaign, Reward $reward): RedirectResponse
    {
        try {
            $reward->delete();
            return redirect()->route('user.campaigns.rewards.index', $campaign->id)
                ->with('success', 'Reward deleted successfully');

        } catch (Exception $e) {
            return back()->with('error', 'Error deleting reward: ' . $e->getMessage());
        }
    }
}
