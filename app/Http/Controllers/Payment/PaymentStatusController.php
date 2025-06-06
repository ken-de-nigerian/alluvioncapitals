<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentStatusController extends Controller
{
    /**
     * Show payment failed page with an error message
     */
    public function failed(Request $request, string $donation)
    {
        return $this->paymentStatusView(
            title: 'Payment Failed',
            defaultMessage: 'We couldn\'t process your payment. This might be due to insufficient funds, incorrect card details, or bank restrictions.
            Please try again with a different payment method or contact your bank for more information.',
            request: $request,
            retry: true,
            donation: $donation
        );
    }

    /**
     * Show payment canceled page with message
     */
    public function cancelled(Request $request, string $donation)
    {
        return $this->paymentStatusView(
            title: 'Payment Cancelled',
            defaultMessage: 'You interrupted the payment process before it was completed.
            If this was accidental, you can restart your donation from the campaign page.',
            request: $request,
            retry: true,
            donation: $donation
        );
    }

    /**
     * Show payment error page with message
     */
    public function error(Request $request, string $donation)
    {
        return $this->paymentStatusView(
            title: 'Payment Processing Error',
            defaultMessage: 'We encountered an unexpected problem while handling your payment,
            Our team has been notified. Please try again later or contact support if the problem persists.',
            request: $request,
            retry: true,
            donation: $donation
        );
    }

    /**
     * Shared view renderer for payment status pages
     */
    protected function paymentStatusView(string $title, string $defaultMessage, Request $request, bool $retry = false, string $donation = null)
    {
        $donation = Donation::with('campaign')->findOrFail($donation);

        // Get any flashed error message from session
        $errorMessage = $request->session()->get('error', $defaultMessage);

        // Get any additional data from the session
        $txRef = $request->session()->get('tx_ref');

        // Build retry URL with proper query parameters
        $retryUrl = route('campaigns.donate', [
            'slug' => $donation->campaign->slug,
            'rewards_id' => $donation->reward_id ?? null,
            'selected-amount' => $donation->amount ?? null,
        ]);

        return Inertia::render('Payment/Status', [
            'title' => $title,
            'errorMessage' => $errorMessage,
            'txRef' => $txRef,
            'sessionData' => $donation,
            'campaign' => $donation->campaign,
            'retry' => $retry,
            'retry_url' => $retryUrl,
        ]);
    }
}
