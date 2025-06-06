<?php

namespace App\Services;

use App\Models\Donation;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

class StripePayment
{
    /**
     * @param float $totalAmount
     * @param Donation $donation
     * @param int $comment_id
     * @return string|null
     * @throws ApiErrorException
     */
    public static function initializeTransaction(float $totalAmount, Donation $donation, int $comment_id): ?string
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $session = Session::create([
            'payment_method_types' => ['card'],
            'customer_email' => $donation->email,
            'line_items' => [[
                'price_data' => [
                    'currency' => 'ngn',
                    'product_data' => [
                        'name' => config('app.name'),
                    ],
                    'unit_amount' => $totalAmount * 100,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('callback.stripe') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('payment.cancelled', $donation->id),
            'metadata' => [
                'donation_id' => $donation->id,
                'comment_id' => $comment_id,
            ]
        ]);

        return $session->url;
    }
}
