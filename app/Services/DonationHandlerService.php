<?php

namespace App\Services;

use App\Models\Donation;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;

class DonationHandlerService
{
    /**
     * Process the donation based on the provided data.
     *
     * @param Donation $donation
     * @param int $comment_id
     * @return array
     */
    public static function processDonation(Donation $donation, int $comment_id = 0): array
    {
        // Add donation fee to amount
        $totalAmount = $donation->amount + config('settings.donation.fixed_fee');

        // Get the selected gateway
        $gateway = $donation->gateway;

        try {
            // Handle donation based on the selected gateway
            return match ($gateway) {
                'Paystack' => self::processPaystackDonation($totalAmount, $donation, $comment_id),
                'Flutterwave' => self::processFlutterwaveDonation($totalAmount, $donation, $comment_id),
                "Monnify" => self::processMonnifyDonation($totalAmount, $donation, $comment_id),
                "Stripe" => self::processStripeDonation($totalAmount, $donation, $comment_id),
                default => [
                    'status' => 'error',
                    'message' => 'Unsupported payment gateway.'
                ],
            };
        } catch (Exception $e) {
            // Log the exception if any error occurs
            Log::error('Donation processing failed: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Handle Paystack donation.
     *
     * @param float $totalAmount
     * @param Donation $donation
     * @param int $comment_id
     * @return array
     * @throws Exception
     */
    private static function processPaystackDonation(float $totalAmount, Donation $donation, int $comment_id): array
    {
        $responseData = Paystack::initializeTransaction(
            $totalAmount,
            $donation,
            $comment_id
        );

        if ($responseData['status'] === true) {
            return [
                'status' => 'success',
                'authorization_url' => $responseData['data']['authorization_url']
            ];
        }

        return [
            'status' => 'error',
            'message' => $responseData['message']
        ];
    }

    /**
     * Handle Flutterwave donation.
     *
     * @param float $totalAmount
     * @param Donation $donation
     * @param int $comment_id
     * @return array
     * @throws Exception
     */
    private static function processFlutterwaveDonation(float $totalAmount, Donation $donation, int $comment_id): array
    {
        $responseData = Flutterwave::initializeTransaction(
            $totalAmount,
            $donation,
            $comment_id
        );

        if ($responseData['status'] === 'success') {
            return [
                'status' => 'success',
                'authorization_url' => $responseData['data']['link']
            ];
        }

        return [
            'status' => 'error',
            'message' => $responseData['message']
        ];
    }

    /**
     * Handle Monnify donation.
     *
     * @param float $totalAmount
     * @param Donation $donation
     * @param int $comment_id
     * @return array
     * @throws ConnectionException
     */
    private static function processMonnifyDonation(float $totalAmount, Donation $donation, int $comment_id): array
    {
        $responseData = Monnify::initializeTransaction(
            $totalAmount,
            $donation,
            $comment_id
        );

        if ($responseData['requestSuccessful'] === true) {
            return [
                'status' => 'success',
                'authorization_url' => $responseData['responseBody']['checkoutUrl']
            ];
        }

        return [
            'status' => 'error',
            'message' => $responseData['message']
        ];
    }

    /**
     * @param float $totalAmount
     * @param Donation $donation
     * @param int $comment_id
     * @return array
     * @throws ApiErrorException
     */
    private static function processStripeDonation(float $totalAmount, Donation $donation, int $comment_id): array
    {
        $responseData = StripePayment::initializeTransaction(
            $totalAmount,
            $donation,
            $comment_id
        );

        return [
            'status' => 'success',
            'authorization_url' => $responseData
        ];
    }
}
