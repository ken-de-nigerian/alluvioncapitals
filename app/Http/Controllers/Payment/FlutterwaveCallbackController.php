<?php

namespace App\Http\Controllers\Payment;

use App\Services\Flutterwave;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FlutterwaveCallbackController extends BasePaymentController
{
    /**
     * Handle Flutterwave payment callback
     */
    public function flutterwave(Request $request): RedirectResponse
    {
        try {
            // Validate and verify the transaction
            $postData = $this->validateFlutterwaveRequest($request);

            if ($postData['status'] === 'cancelled') {
                Log::info("Payment cancelled by user", ['tx_ref' => $postData['tx_ref']]);

                $donation_id = session()->pull('campaign.donation.details.donation_id');
                return redirect()->route('payment.cancelled', $donation_id);
            }

            $responseData = Flutterwave::verifyTransaction($postData['transaction_id']);

            // Check if the transaction was successful
            if (!$this->isFlutterwaveTransactionSuccessful($responseData)) {
                return $this->failedFlutterwaveTransactionResponse($responseData);
            }

            // Extract transaction details
            $transactionDetails = $this->extractFlutterwaveTransactionDetails($responseData);

            // Get donation
            $donation = $this->getDonationOrFail($transactionDetails['donation_id']);

            // Conditionally update comment
            if (!empty($transactionDetails['comment_id'])) {
                $this->updateDonorCommentIfExists($transactionDetails['comment_id']);
            }

            // Update donation and campaign
            $this->updateDonationRecord($transactionDetails);
            $this->updateCampaignFunds($donation->campaign_id, $donation->amount);
            $this->updateUserFunds($donation->campaign->user_id, $donation->amount);

            // Redirect to success page with donation_id
            return $this->successfulDonationResponse($donation->id, $donation->campaign_id);

        } catch (Exception $e) {
            Log::error('Flutterwave Callback Error: ' . $e->getMessage());

            $donation_id = session()->pull('campaign.donation.details.donation_id');
            return redirect()->route('payment.failed', $donation_id)
                    ->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Validate Flutterwave request.
     */
    protected function validateFlutterwaveRequest(Request $request): array
    {
        return $request->validate([
            'status' => 'required|string|in:successful,completed,failed,cancelled',
            'transaction_id' => 'required_if:status,successful,completed',
            'tx_ref' => 'required|string'
        ]);
    }

    /**
     * Extract relevant details from Flutterwave response.
     */
    private function extractFlutterwaveTransactionDetails(array $responseData): array
    {
        $meta = $responseData['data']['meta'] ?? [];

        return [
            'reference' => $responseData['data']['tx_ref'],
            'channel' => $responseData['data']['payment_type'],
            'amount' => $responseData['data']['amount'],
            'donation_id' => $meta['donation_id'] ?? null,
            'comment_id' => $meta['comment_id'] ?? null,
        ];
    }

    /**
     * Check if the Flutterwave transaction is successful.
     */
    protected function isFlutterwaveTransactionSuccessful(array $responseData): bool
    {
        return $responseData['status'] == 'success';
    }

    /**
     * Redirect to failed transaction page (without a donation_id fallback).
     */
    protected function failedFlutterwaveTransactionResponse(array $responseData): RedirectResponse
    {
        $donationId = $responseData['data']['meta']['donation_id'] ?? null;

        if ($donationId) {
            return redirect()->route('payment.failed', $donationId)
                ->with('error', $responseData['message']);
        }

        return redirect()->route('campaigns.index')
            ->with('error', $responseData['message']);
    }
}
