<?php

namespace App\Http\Controllers\Payment;

use App\Services\Paystack;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaystackCallbackController extends BasePaymentController
{
    /**
     * Handle Paystack payment callback.
     */
    public function paystack(Request $request): RedirectResponse
    {
        try {
            // Validate and verify the transaction
            $postData = $this->validatePaystackRequest($request);
            $responseData = Paystack::verifyTransaction($postData['reference']);

            // Check if the transaction was successful
            if (!$this->isTransactionSuccessful($responseData)) {
                return $this->failedTransactionResponse($responseData);
            }

            // Extract transaction details
            $transactionDetails = $this->extractPaystackTransactionDetails($responseData);

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
            Log::error('Paystack Callback Error: ' . $e->getMessage());

            $donation_id = session()->pull('campaign.donation.details.donation_id');
            return redirect()->route('payment.failed', $donation_id)
                ->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Validate Paystack request.
     */
    protected function validatePaystackRequest(Request $request): array
    {
        return $request->validate([
            'trxref' => 'required|string',
            'reference' => 'required|string',
        ]);
    }

    /**
     * Extract relevant details from Paystack response.
     */
    private function extractPaystackTransactionDetails(array $responseData): array
    {
        $metadata = $responseData['data']['metadata'] ?? [];

        return [
            'reference' => $responseData['data']['reference'],
            'channel' => $responseData['data']['channel'],
            'amount' => $responseData['data']['amount'] / 100,
            'donation_id' => $metadata['donation_id'] ?? null,
            'comment_id' => $metadata['comment_id'] ?? null,
        ];
    }

    /**
     * Check if the Paystack transaction is successful.
     */
    protected function isTransactionSuccessful(array $responseData): bool
    {
        return $responseData['status'] == 1 && $responseData['data']['status'] === 'success';
    }

    /**
     * Redirect to failed transaction page (without a donation_id fallback).
     */
    protected function failedTransactionResponse(array $responseData): RedirectResponse
    {
        $donationId = $responseData['data']['metadata']['donation_id'] ?? null;

        if ($donationId) {
            return redirect()->route('payment.failed', $donationId)
                ->with('error', $responseData['message']);
        }

        return redirect()->route('campaigns.index')
            ->with('error', $responseData['message']);
    }
}
