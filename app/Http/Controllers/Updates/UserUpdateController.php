<?php

namespace App\Http\Controllers\Updates;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Update;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class UserUpdateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Campaign $campaign)
    {
        // Updates associated with this campaign
        $updates = $campaign->updates()->latest()->paginate(5);

        return Inertia::render('User/Updates/Index', [
            'updates' => $updates,
            'campaign' => $campaign,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Campaign $campaign)
    {
        return Inertia::render('User/Updates/Create', [
            'campaign' => $campaign,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Campaign $campaign)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpg,jpeg,png|max:5120',
        ]);

        try {
            // Handle file uploads
            $photos = [];

            // Store new photos with full URLs
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $storedPath = $photo->store('campaign-updates', 'public');
                    $photos[] = Storage::disk('public')->url($storedPath);
                }
            }

            $campaign->updates()->create([
                'title' => $request->input('title'),
                'message' => $request->input('message'),
                'images' => json_encode($photos),
                'status' => 'active',
            ]);

            return redirect()
                ->route('user.campaigns.updates.index', $campaign->id)
                ->with('success', 'Campaign update added successfully! Supporters will appreciate this.');

        } catch (Exception $exception) {
            Log::error('Failed to add campaign update: ' . $exception->getMessage());

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
        $update = Update::with('campaign')->find($id);

        return Inertia::render('User/Updates/Edit', [
            'update' => $update,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Update $update)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'photos.*' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            'existing_photos' => 'nullable|array',
            'status' => 'required'
        ]);

        try {

            // Handle file uploads
            $photos = [];

            // Store new photos with full URLs
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $storedPath = $photo->store('campaign-updates', 'public');
                    $photos[] = Storage::disk('public')->url($storedPath);
                }
            }

            // Keep existing photos (assuming these are already full URLs)
            if ($request->has('existing_photos')) {
                $photos = array_merge($photos, $request->input('existing_photos'));
            }

            $oldImages = json_decode($update->images, true) ?? [];
            $removedImages = array_diff($oldImages, $request->input('existing_photos', []));
            foreach ($removedImages as $img) {
                Storage::disk('public')->delete($img);
            }

            $update->update([
                'title' => $request->input('title'),
                'message' => $request->input('message'),
                'images' => json_encode($photos),
            ]);

            return redirect()
                ->route('user.campaigns.updates.index', $update->campaign_id)
                ->with('success', 'Campaign update modified successfully.');

        } catch (Exception $e) {
            Log::error("Failed to update campaign update: " . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Failed to update. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Campaign $campaign, Update $update)
    {
        try {
            $update->delete();
            return redirect()->route('user.campaigns.updates.index', $campaign->id)
                ->with('success', 'Update deleted successfully');

        } catch (Exception $e) {
            return back()->with('error', 'Error deleting update: ' . $e->getMessage());
        }
    }
}
