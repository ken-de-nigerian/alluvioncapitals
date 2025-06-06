<?php

namespace App\Http\Controllers\Payment;

use App\Services\Monnify;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MonnifyCallbackController extends BasePaymentController
{
    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws Exception
     */
    public function monnify(Request $request): RedirectResponse
    {
        try {
            $request->validate([
                'paymentReference' => 'required|string'
            ]);

            $responseData = Monnify::verifyTransaction($request->input('paymentReference'));

            if ($responseData['responseBody']['paymentStatus'] === "PAID") {

                $transactionDetails = $this->extractMonnifyTransactionDetails($responseData);

                $donation = $this->getDonationOrFail($transactionDetails['donation_id']);

                $this->updateDonorCommentIfExists($transactionDetails['comment_id']);

                $this->updateDonationRecord($transactionDetails);
                $this->updateCampaignFunds($donation->campaign_id, $donation->amount);
                $this->updateUserFunds($donation->campaign->user_id, $donation->amount);

                return $this->successfulDonationResponse($donation->id, $donation->campaign_id);
            }

            $donation_id = session()->pull('campaign.donation.details.donation_id');
            if ($responseData['responseBody']['paymentStatus'] == 'PENDING') {
                Log::info("Payment cancelled by user", ['tx_ref' => $request->input('paymentReference')]);
                return redirect()
                    ->route('payment.cancelled', $donation_id)
                    ->with('tx_ref', $request->input('paymentReference'));
            }

            Log::error("Payment failed", ['status' => $responseData['responseBody']['paymentStatus'], 'tx_ref' => $request->input('paymentReference')]);
            return redirect()
                ->route('payment.failed', $donation_id)
                ->with([
                    'error' => 'Payment failed. Please try again.',
                    'tx_ref' => $request->input('paymentReference')
                ]);
        }catch (Exception $exception) {
            $donation_id = session()->pull('campaign.donation.details.donation_id');
            return redirect()->route('payment.error', $donation_id)
                ->with('error', $exception->getMessage());
        }
    }

    /**
     * @param array $responseData
     * @return array
     */
    private function extractMonnifyTransactionDetails(array $responseData): array
    {
        return [
            'reference' => $responseData['responseBody']['paymentReference'],
            'channel' => $responseData['responseBody']['paymentMethod'],
            'amount' => $responseData['responseBody']['amount'],
            'donation_id' => $responseData['responseBody']['metaData']['donation_id'],
            'comment_id' => $responseData['responseBody']['metaData']['comment_id'],
        ];
    }
}
