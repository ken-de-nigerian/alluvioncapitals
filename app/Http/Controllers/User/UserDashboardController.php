<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetConfirmation;
use App\Models\Campaign;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Intervention\Image\Laravel\Facades\Image;
use Jenssegers\Agent\Agent;

class UserDashboardController extends Controller
{
    /**
     * @return Response
     */
    public function index()
    {
        return Inertia::render('User/Index', [
            'user' => Auth::user()->only(
                'id',
                'first_name',
                'last_name',
                'email',
                'phone_number',
                'balance',
                'email_verified_at'
            ),
            'profile_progress' => $this->calculateProfileProgress(Auth::user()),
            'campaigns' => $this->getActiveCampaigns(),
        ]);
    }

    /**
     * Renders profile tab
     *
     * @return Response
     */
    public function profilePage()
    {
        $user = Auth::user()->only(
            'id',
            'first_name',
            'last_name',
            'email',
            'phone_number',
            'email_verified_at',
            'avatar'
        );
        return Inertia::render('User/Profile/Index', [
            'user' => $user,
        ]);
    }

    /**
     * Renders profile-security tab
     *
     * @return Response
     */
    public function securityPage(Request $request)
    {
        return Inertia::render('User/Profile/Security', [
            'activeSessions' => function () use ($request) {
                if ($request->user()) {
                    $sessions = DB::table('sessions')
                        ->where('user_id', $request->user()->id)
                        ->get();

                    $agent = new Agent();
                    $activeSessions = [];

                    foreach ($sessions as $session) {
                        $agent->setUserAgent($session->user_agent);

                        $activeSessions[] = [
                            'id' => $session->id,
                            'ip_address' => $session->ip_address,
                            'device' => $agent->device(),
                            'platform' => $agent->platform(),
                            'browser' => $agent->browser(),
                            'last_activity' => Carbon::createFromTimestamp($session->last_activity)
                                ->diffForHumans(),
                            'is_current' => $session->id === $request->session()->getId(),
                        ];
                    }

                    return $activeSessions;
                }

                return [];
            },
            'connectedAccounts' => Auth::user()->social_login_provider
        ]);
    }

    /**
     * Update the user's profile picture.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateProfilePicture(Request $request): RedirectResponse
    {
        try {
            $request->validate([
                'profile_image' => [
                    'required',
                    'image',
                    'mimes:png,jpg,jpeg',
                    'max:2048',
                ],
            ], [
                'profile_image.required' => 'Please select an image to upload.',
                'profile_image.image' => 'The file must be an image.',
                'profile_image.mimes' => 'The image must be a PNG, JPG, or JPEG file.',
                'profile_image.max' => 'The image must not exceed 2MB in size.',
            ]);

            $user = Auth::user();
            $storagePath = 'profile/admin/';

            // Delete old image if exists
            if ($user->avatar) {
                $oldImagePath = str_replace(Storage::disk('public')->url(''), '', $user->avatar);
                Storage::disk('public')->delete($oldImagePath);
            }

            // Store new image
            $image = $request->file('profile_image');
            $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
            $fullPath = $storagePath . $filename;

            // Resize and save
            $resizedImage = Image::read($image)->resize(124, 124);
            Storage::disk('public')->put($fullPath, $resizedImage->encode());

            // Update user
            $user->update([
                'avatar' => Storage::disk('public')->url($fullPath)
            ]);

            return redirect()->back()->with([
                'success' => __('Profile picture successfully uploaded.'),
                'auth' => ['user' => $user->fresh()]
            ]);

        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        } catch (Exception $e) {
            Log::error("Profile picture update error: " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to upload image.']);
        }
    }

    /**
     * Remove the user's profile picture.
     *
     * @return RedirectResponse
     */
    public function removeProfilePicture(): RedirectResponse
    {
        $user = Auth::user();

        if ($user->avatar) {
            try {
                $oldImagePath = str_replace(Storage::disk('public')->url(''), '', $user->avatar);
                Storage::disk('public')->delete($oldImagePath);
            } catch (Exception $e) {
                Log::error("Profile picture deletion error: " . $e->getMessage());
                return redirect()->back()->withErrors(['error' => 'Failed to delete the profile picture.']);
            }
        }

        $user->update(['avatar' => null]);

        return redirect()->back()->with([
            'success' => __('Profile picture removed successfully.'),
            'auth' => ['user' => $user->fresh()]
        ]);
    }

    /**
     * Updates Account Information
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            // Update the admin's profile
            Auth::user()->update([
                'first_name' => $request->input('first_name'),
                'last_name' => $request->input('last_name'),
                'phone_number' => $request->input('phone_number'),
            ]);

            return redirect()->back()->with('success', __('Your account details have been updated successfully.'));

        } catch (Exception) {
            return redirect()->back()->with('error', __('Failed to update profile'));
        }
    }

    /**
     * Update the user's password.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        // Validate the request data
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
        ], [
            'current_password.required' => 'Current password is required.',
            'password.required' => 'New password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        // Verify the current password
        if (!Hash::check($request->input('current_password'), Auth::user()->password)) {
            return redirect()->back()->with('error', __('The current password is incorrect.'))->withInput();
        }

        // Update the user's password
        Auth::user()->update([
            'password' => Hash::make($request->input('password')),
        ]);

        // Send email
        if (config('settings.email_notification')) {
            Mail::mailer(config('settings.email_provider'))->to(Auth::user()->email)->send(new PasswordResetConfirmation(Auth::user()));
        }

        // Return appropriate response
        return redirect()->back()
            ->with('success', __('Your password has been updated successfully.'));
    }

    /**
     * @return Response
     */
    public function notificationSettings()
    {
        return Inertia::render('User/Profile/NotificationSettings', [
            'notificationSettings' => Auth::user()->notification_settings
        ]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateNotifications(Request $request)
    {
        $request->validate([
            'donation_received' => 'boolean',
            'campaign_updated' => 'boolean',
            'newsletter' => 'boolean',
            'campaign_review' => 'boolean',
            'daily_summary' => 'boolean',
        ]);

        $user = Auth::user();
        $user->notification_settings = array_merge(
            $user->notification_settings ?? [],
            $request->only([
                'donation_received',
                'campaign_updated',
                'newsletter',
                'campaign_review',
                'daily_summary',
            ])
        );
        $user->save();

        return back()->with('success', 'Notification preferences updated.');
    }

    /**
     * Calculate the profile completion percentage.
     *
     * @param User $user
     * @return float
     */
    private function calculateProfileProgress(User $user): float
    {
        $requiredFields = [
            'first_name',
            'last_name',
            'email',
            'phone_number',
            'email_verified_at'
        ];

        $totalFields = count($requiredFields);
        $filledFields = 0;

        foreach ($requiredFields as $field) {
            if (!empty($user->$field)) {
                $filledFields++;
            }
        }

        return round(($filledFields / $totalFields) * 100);
    }

    /**
     * Get active campaigns with search and pagination.
     */
    private function getActiveCampaigns()
    {
        $campaigns = Campaign::where('user_id', Auth::user()->id)
            ->inRandomOrder()
            ->latest()
            ->paginate(4);

        $campaigns->getCollection()->transform(fn($campaign) => $this->cleanCampaign($campaign));

        return $campaigns;
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
}
