<?php

namespace App\Services;

use App\Models\Donation;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Paystack
{
    /**
     * Initializes a transaction with Paystack.
     *
     * @param int|float $amount Amount in naira (multiplied by 100 for kobo).
     * @param Donation $donation
     * @param int $comment_id
     * @return array Response from Paystack.
     * @throws Exception If an error occurs during the request.
     */
    public static function initializeTransaction(int|float $amount, Donation $donation, int $comment_id): array
    {
        self::validateEnvironment();
        self::validateAmount($amount);

        try {
            // Prepare API request
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.paystack.secret_key'),
                'Cache-Control' => 'no-cache',
            ])->post('https://api.paystack.co/transaction/initialize', [
                'email' => $donation->email,
                'amount' => $amount * 100, // Amount in kobo
                'callback_url' => route('callback.paystack'),
                'metadata' => [
                    'cancel_action' => route('payment.cancelled', $donation->id),
                    'donation_id' => $donation->id,
                    'comment_id' => $comment_id
                ]
            ]);

            return self::handleResponse($response);
        } catch (Exception $e) {
            Log::error('Paystack transaction initialization failed: ' . $e->getMessage());
            throw new Exception('Failed to initialize Paystack transaction: ' . $e->getMessage());
        }
    }

    /**
     * Verifies a Paystack transaction.
     *
     * @param string $reference Transaction reference from Paystack.
     * @return array Response from Paystack.
     * @throws Exception If an error occurs during the request.
     */
    public static function verifyTransaction(string $reference): array
    {
        self::validateEnvironment();

        try {
            // Verify transaction with Paystack
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.paystack.secret_key'),
                'Cache-Control' => 'no-cache',
            ])->get("https://api.paystack.co/transaction/verify/$reference");

            return self::handleResponse($response);
        } catch (Exception $e) {
            Log::error('Paystack transaction verification failed: ' . $e->getMessage());
            throw new Exception('Failed to verify Paystack transaction: ' . $e->getMessage());
        }
    }

    /**
     * Fetches the list of banks from the Paystack API, caching the result for 24 hours.
     *
     * @return array The list of banks returned by the Paystack API.
     * @throws Exception If an error occurs during the API call or response processing.
     */
    public static function fetchBanks(): array
    {
        self::validateEnvironment();

        // Define cache key and duration (24 hours = 1440 minutes)
        $cacheKey = 'paystack_banks';
        $cacheDuration = 1440;

        // Attempt to retrieve banks from cache
        return Cache::remember($cacheKey, $cacheDuration, function () {
            try {
                // Fetch banks from Paystack
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . config('services.paystack.secret_key'),
                    'Cache-Control' => 'no-cache',
                ])->get('https://api.paystack.co/bank');

                $data = self::handleResponse($response);
                $banks = $data['data'] ?? [];

                // Map banks to include only code and name
                return array_map(function ($bank) {
                    return [
                        'code' => $bank['code'] ?? '',
                        'name' => $bank['name'] ?? ''
                    ];
                }, $banks);
            } catch (Exception $e) {
                Log::error('Paystack bank list fetch failed: ' . $e->getMessage());
                throw new Exception('Failed to fetch banks from Paystack: ' . $e->getMessage());
            }
        });
    }

    /**
     * Resolves account details using the Paystack API.
     *
     * @param string $accountNumber The account number to resolve.
     * @param string $bankCode The bank code associated with the account.
     * @return array An array containing the resolved account details or an error message.
     * @throws Exception If an error occurs during the API call or response processing.
     */
    public static function resolveAccountDetails(string $accountNumber, string $bankCode): array
    {
        self::validateEnvironment();

        try {
            // Resolve account details using Paystack
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.paystack.secret_key'),
                'Cache-Control' => 'no-cache',
            ])->get("https://api.paystack.co/bank/resolve", [
                'account_number' => $accountNumber,
                'bank_code' => $bankCode,
            ]);

            $data = self::handleResponse($response);
            return $data['data'] ?? [];
        } catch (Exception $e) {
            Log::error('Paystack account resolution failed: ' . $e->getMessage());
            throw new Exception('Failed to resolve account details: ' . $e->getMessage());
        }
    }

    /**
     * Validate that required environment variables are set
     *
     * @throws Exception
     */
    protected static function validateEnvironment(): void
    {
        if (empty(config('services.paystack.secret_key'))) {
            throw new Exception('Paystack secret key is not configured.');
        }
    }

    /**
     * Validate that the amount is greater than zero
     *
     * @param int|float $amount
     * @throws Exception
     */
    protected static function validateAmount(int|float $amount): void
    {
        if ($amount <= 0) {
            throw new Exception('Amount must be greater than zero.');
        }
    }

    /**
     * Handle the API response
     *
     * @param Response $response
     * @return array
     * @throws Exception
     */
    protected static function handleResponse(Response $response): array
    {
        if ($response->failed()) {
            $error = $response->json();
            $message = $error['message'] ?? 'Paystack API request failed';
            throw new Exception($message);
        }

        return $response->json();
    }
}
