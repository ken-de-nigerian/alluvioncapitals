<?php

namespace App\Http\Controllers\Donations;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Comment;
use App\Models\Donation;
use App\Models\Gateway;
use App\Models\Reward;
use App\Services\DonationHandlerService;
use Exception;
use http\Exception\InvalidArgumentException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class DonationController extends Controller
{
    /**
     * @param string $slug
     * @return RedirectResponse|Response
     */
    public function donate(string $slug): RedirectResponse|Response
    {
        // Check if the selected-amount exists in the url
        if (empty(request('selected-amount'))) {
            return redirect()
                ->route('campaigns.show', ['slug' => $slug])
                ->with('error', 'Please enter your donation amount first');
        }

        // Show Campaign Details
        $campaign = Campaign::with('category')
            ->where('slug', $slug)
            ->firstOrFail();

        // Get the parameters from url
        $reward_id = request('rewards_id');
        $selected_amount = request('selected-amount');

        // Get Payment Gateways
        $gateways = Gateway::where('status', 'active')->orderBy('name')->get();

        // Get Reward Details
        $reward = Reward::with('campaign')->where('id', $reward_id)->first();

        return Inertia::render('Donations/Donate', [
            'campaign' => $campaign,
            'selected_amount' => $selected_amount,
            'reward' => $reward,
            'gateways' => $gateways,
        ]);
    }

    /**
     * Process a donation payment and return a JSON response.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function makePayment(Request $request): JsonResponse
    {
        // Validate the input
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'phone_number' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'anonymous' => 'nullable|boolean',
            'gateway' => 'required|string|in:bankTransfer,' . implode(',', DB::table('gateways')->pluck('name')->toArray()),
            'slug' => 'required|string|exists:campaigns,slug',
            'accept_terms' => 'required|accepted',
            'comments' => 'nullable|string',
            'reward_id' => 'nullable|exists:rewards,id',
            'country' => 'sometimes|string|max:255',
            'state' => 'sometimes|string|max:255',
            'city' => 'sometimes|string|max:255',
            'address' => 'sometimes|string|max:255',
            'postal_code' => 'sometimes|string|max:255',
            'requires_shipping' => 'nullable|boolean',
        ]);

        // Return validation errors as JSON
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validatedData = $validator->validated();

        // Check for reward amount discrepancy
        if (!empty($validatedData['reward_id'])) {
            $reward = Reward::with('campaign')->find($validatedData['reward_id']);
            if ($validatedData['amount'] < $reward->amount) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'amount' => ['The donation amount cannot be less than ₦' . number_format($reward->amount, 2)],
                    ],
                ], 400);
            }
        }

        // Validate donation amount
        $minAmount = config('settings.donation.min_amount', 100);
        $maxAmount = config('settings.donation.max_amount', 1000000);

        if ($validatedData['amount'] < $minAmount) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'amount' => ['The minimum donation amount must be at least ₦' . number_format($minAmount, 2)],
                ],
            ], 400);
        }

        if ($validatedData['amount'] > $maxAmount) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'amount' => ['The donation amount cannot exceed ₦' . number_format($maxAmount, 2)],
                ],
            ], 400);
        }

        try {
            // Get Campaign
            $campaign = $this->getCampaignOrFail($validatedData['slug']);

            // Add campaign_id to validated data
            $validatedData['campaign_id'] = $campaign->id;

            // Store donation details
            $donation = Donation::create($validatedData);

            // Save comment and extract id
            $comment_id = $this->saveDonorCommentIfExists($validatedData, $campaign->id);

            // store donation details to session
            $request->session()->put('campaign.donation.details', [
                'donation_id' => $donation->id,
            ]);

            // Process the donation
            $result = (new DonationHandlerService())->processDonation($donation, $comment_id);

            return response()->json([
                'status' => 'success',
                'redirect_url' => $result['authorization_url'],
            ]);
        } catch (Exception $e) {
            Log::error('Donation Failed: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing your donation. Please try again later.',
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function success(Request $request)
    {
        try {

            // Validate required query parameters
            if (!$request->has(['donationId', 'campaignId'])) {
                throw new InvalidArgumentException('Missing required parameters');
            }

            $donationId = $request->query('donationId');
            $campaignId = $request->query('campaignId');

            if (!is_numeric($donationId) || !is_numeric($campaignId) || $donationId < 1 || $campaignId < 1) {
                throw new InvalidArgumentException('Invalid parameters');
            }

            // Get data with only necessary relationships and columns
            $campaign = Campaign::with('category')
                ->where('id', $campaignId)
                ->firstOrFail();

            // Get Donation Details
            $donation = Donation::where('id', $donationId)
                ->firstOrFail();

            // Get Related Campaigns
            $relatedCampaigns = Campaign::with('category')
                ->where('category_id', $campaign->category->id)
                ->where('id', '!=', $campaign->id)
                ->limit(2)->get();

            // If not found
            if ($relatedCampaigns->isEmpty()) {
                $relatedCampaigns = Campaign::with('category')
                    ->where('id', '!=', $campaign->id)
                    ->inRandomOrder()
                    ->limit(2)->get();
            }

            return Inertia::render('Donations/Status', [
                'campaign' => $campaign,
                'donation' => $donation,
                'relatedCampaigns' => $relatedCampaigns,
                'showConfetti' => session('show_confetti', false),
            ]);

        } catch (InvalidArgumentException) {
            return redirect()
                ->route('campaigns')
                ->with('error', 'Invalid donation reference. Please check your URL.');

        } catch (ModelNotFoundException) {
            return redirect()
                ->route('campaigns')
                ->with('error', 'Donation or campaign not found. Please contact support.');

        } catch (Exception $e) {
            Log::error('Donation success error', [
                'error' => $e->getMessage(),
                'params' => $request->query()
            ]);

            return redirect()
                ->route('campaigns')
                ->with('error', 'We encountered an error processing your donation receipt.');
        }
    }

    /**
     * @param string $slug
     * @return Campaign
     */
    private function getCampaignOrFail(string $slug): Campaign
    {
        $campaign = Campaign::where('slug', $slug)->first();

        if (!$campaign) {
            throw new RuntimeException('Campaign not found.');
        }

        return $campaign;
    }

    /**
     * Save a donor comment if provided in validated data.
     *
     * @param array $validatedData
     * @param int $campaign_id
     * @return int
     */
    private function saveDonorCommentIfExists(array $validatedData, int $campaign_id): int
    {
        if (empty($validatedData['comments'])) {
            return 0;
        }

        $comment = Comment::create([
            'campaign_id' => $campaign_id,
            'first_name' => $validatedData['first_name'] ?? '',
            'last_name' => $validatedData['last_name'] ?? '',
            'email' => $validatedData['email'] ?? '',
            'comment' => $validatedData['comments'],
            'anonymous' => $validatedData['anonymous'] ?? false,
        ]);

        return $comment->id;
    }
}
