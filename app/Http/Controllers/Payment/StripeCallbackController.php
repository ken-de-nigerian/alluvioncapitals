<?php

namespace App\Http\Controllers\Payment;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripeCallbackController extends BasePaymentController
{
    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function stripe(Request $request): RedirectResponse
    {
        try {
            $request->validate([
                'session_id' => 'required|string'
            ]);

            Stripe::setApiKey(config('services.stripe.secret'));
            $responseData = Session::retrieve($request->get('session_id'));

            if ($responseData['payment_status'] === 'paid' && $responseData['status'] === 'complete') {

                $transactionDetails = $this->extractStripeTransactionDetails($responseData);

                $donation = $this->getDonationOrFail($transactionDetails['donation_id']);

                $this->updateDonorCommentIfExists($transactionDetails['comment_id']);

                $this->updateDonationRecord($transactionDetails);
                $this->updateCampaignFunds($donation->campaign_id, $donation->amount);
                $this->updateUserFunds($donation->campaign->user_id, $donation->amount);

                return $this->successfulDonationResponse($donation->id, $donation->campaign_id);
            }

            $donation_id = session()->pull('campaign.donation.details.donation_id');
            Log::error("Payment failed", ['status' => $responseData['status'], 'tx_ref' => $responseData['payment_intent']]);
            return redirect()
                ->route('payment.failed', $donation_id)
                ->with([
                    'error' => 'Payment failed. Please try again.',
                    'tx_ref' => $responseData['payment_intent']
                ]);

        } catch (Exception $exception) {
            $donation_id = session()->pull('campaign.donation.details.donation_id');
            return redirect()->route('payment.error', $donation_id)
                ->with('error', $exception->getMessage());
        }
    }

    /**
     * @param Session $responseData
     * @return array
     */
    private function extractStripeTransactionDetails(Session $responseData): array
    {
        return [
            'reference' => $responseData['payment_intent'],
            'channel' => implode(',', $responseData['payment_method_types']),
            'amount' => $responseData['amount_total'] / 100,
            'donation_id' => $responseData['metadata']['donation_id'],
            'comment_id' => $responseData['metadata']['comment_id'],
        ];
    }
}
