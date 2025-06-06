<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Comment;
use App\Models\Donation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use RuntimeException;

abstract class BasePaymentController extends Controller
{
    /**
     * @param int $id
     * @return Donation
     */
    protected function getDonationOrFail(int $id): Donation
    {
        $donation = Donation::with('campaign')->where('id', $id)->first();

        if (!$donation) {
            throw new RuntimeException('Donation not found.');
        }

        return $donation;
    }

    /**
     * @param int $id
     * @param float $amount
     * @return void
     */
    protected function updateCampaignFunds(int $id, float $amount): void
    {
        $campaign = Campaign::where('id', $id)->first();

        $campaign->funds_raised += $amount;
        $campaign->save();
    }

    /**
     * @param int $id
     * @param float $amount
     * @return void
     */
    protected function updateUserFunds(int $id, float $amount): void
    {
        $user = User::where('id', $id)->first();

        $user->balance += $amount;
        $user->save();
    }

    /**
     * @param int $id
     * @return bool
     */
    public function updateDonorCommentIfExists(int $id): bool
    {
        $comment = Comment::where('id', $id)->first();

        if ($comment) {
            return $comment->update([
                'status' => 'active'
            ]);
        }

        return false;
    }

    /**
     * @param array $transactionDetails
     * @return void
     */
    protected function updateDonationRecord(array $transactionDetails): void
    {
        Donation::where('id', $transactionDetails['donation_id'])->update([
            'channel' => $transactionDetails['channel'],
            'transaction_reference' => $transactionDetails['reference'],
            'status' => 'approved',
        ]);
    }

    /**
     * @param int $donation
     * @param int $campaign
     * @return RedirectResponse
     */
    protected function successfulDonationResponse(int $donation, int $campaign): RedirectResponse
    {
        session()->flash('show_confetti');

        return redirect()->route('campaigns.status.success', [
            'donationId' => $donation,
            'campaignId' => $campaign,
        ])->with('success', 'Thank you for your generosity! Your donation has been successfully processed.');
    }
}
