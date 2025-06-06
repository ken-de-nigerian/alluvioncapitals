<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Mail\WithdrawalConfirmation;
use App\Models\Donation;
use App\Models\Withdrawal;
use App\Models\WithdrawalSettings;
use App\Rules\ValidBankAccount;
use App\Services\Paystack;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;
use Throwable;

class DonationsAndWithdrawalsController extends Controller
{
    /**
     * @return Response
     */
    public function index()
    {
        $donations = Donation::query()
            ->whereHas('campaign', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->when(request('search'), function ($query, $search) {
                $query->where('amount', 'like', '%' . $search . '%')
                    ->orWhereHas('campaign', function ($query) use ($search) {
                        $query->where('title', 'like', '%' . $search . '%')
                            ->orWhere('slug', 'like', '%' . $search . '%');
                    });
            })
            ->select([
                'amount',
                'gateway',
                'created_at',
                'status',
                'first_name',
                'last_name',
                'campaign_id',
            ])
            ->with(['campaign' => function ($query) {
                $query->select('id', 'title', 'slug', 'campaign_images');
            }])
            ->latest()
            ->paginate(8)
            ->withQueryString();

        return Inertia::render('User/Donations/Index', [
            'donations' => $donations,
            'filters' => request()->all('search')
        ]);
    }

    /**
     * @throws Exception
     */
    public function withdrawals()
    {
        $withdrawal_details = WithdrawalSettings::where('user_id', Auth::id())->first();
        $withdrawals = Withdrawal::with('withdrawal_settings')
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate('8');
        $banks = Paystack::fetchBanks();

        return Inertia::render('User/Withdrawals/Index', [
            'user' => Auth::user()->only('balance'),
            'withdrawal_details' => $withdrawal_details ? $withdrawal_details->toArray() : null,
            'withdrawals' => $withdrawals,
            'banks' => $banks,
        ]);
    }

    /**
     * Handle a withdrawal request.
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws Throwable
     */
    public function requestWithdrawal(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'amount' => [
                'required',
                'numeric',
                'min:100',
                'max:' . $user->balance,
            ],
        ]);

        $withdrawalDetails = WithdrawalSettings::where('user_id', $user->id)->first();

        if (!$withdrawalDetails) {
            return redirect()->route('user.payments.withdrawals')
                ->with('error', 'Please add a payment method before requesting a withdrawal.');
        }

        try {
            DB::transaction(function () use ($user, $request, $withdrawalDetails) {
                Withdrawal::create([
                    'user_id' => $user->id,
                    'withdrawal_settings_id' => $withdrawalDetails->id,
                    'amount' => $request->amount,
                    'status' => 'pending',
                ]);

                $user->decrement('balance', $request->amount);
            });

            // Send email
            if (config('settings.email_notification')){
                Mail::mailer(config('settings.email_provider'))->to($user->email)->send(new WithdrawalConfirmation($user, $withdrawalDetails));
            }

            return redirect()->back()->with('success', 'Your withdrawal request has been submitted successfully.');
        } catch (Exception $e) {
            Log::error('Failed to process withdrawal request: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'amount' => $request->amount,
            ]);
            return redirect()->back()->with('error', 'Unable to process your withdrawal request. Please try again later.');
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function resolveAccount(Request $request)
    {
        try {
            $validated = $request->validate([
                'bank_code' => 'required|string|max:255',
                'account_number' => 'required|digits_between:10,16',
            ]);

            $accountDetails = Paystack::resolveAccountDetails(
                $validated['account_number'],
                $validated['bank_code']
            );

            if (!$accountDetails || !isset($accountDetails['account_name'])) {
                throw new Exception('Invalid response from payment provider');
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'account_number' => $validated['account_number'],
                    'account_name' => $accountDetails['account_name'],
                    'bank_code' => $validated['bank_code'],
                    'bank_name' => $accountDetails['bank_name'] ?? null,
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Account resolution failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Could not resolve account details. Please check the details and try again.',
            ], 500);
        }
    }

    /**
     * @throws Throwable
     */
    public function addAccount(Request $request)
    {
        $validated = $request->validate([
            'bank_code' => ['required', 'string', 'max:255'],
            'bank_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'digits_between:10,16', new ValidBankAccount],
            'account_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
        ]);

        // Verify account details match what was resolved earlier
        if (!$this->verifyAccountDetails($validated)) {
            return back()->with('error', 'Account verification failed. Please re-validate your details.');
        }

        // Check for duplicates using hashed value
        if (WithdrawalSettings::existsForUser(auth()->id(), $validated['account_number'])) {
            return back()->with('error', 'This account already exists in your withdrawal methods.');
        }

        try {
            DB::transaction(function () use ($validated) {
                // Deactivate previous defaults
                WithdrawalSettings::where('user_id', auth()->id())
                    ->where('is_default', true)
                    ->update(['is_default' => false]);

                // Create new record (hash is auto-generated by model)
                WithdrawalSettings::create([
                    'user_id' => auth()->id(),
                    'bank_code' => $validated['bank_code'],
                    'bank_name' => $validated['bank_name'],
                    'account_number' => $validated['account_number'], // Will be encrypted
                    'account_name' => $validated['account_name'],
                    'is_default' => true,
                    'verified_at' => now(),
                ]);
            });

            return back()->with('success', 'Withdrawal account details added successfully!');

        } catch (Exception $e) {
            Log::error('Account addition failed: '.$e->getMessage());
            return back()->with('error', 'Failed to add account. Please try again.');
        }
    }

    /**
     * @param array $data
     * @return bool
     */
    protected function verifyAccountDetails(array $data): bool
    {
        try {
            // Resolve account details from Paystack
            $resolvedDetails = Paystack::resolveAccountDetails(
                $data['account_number'],
                $data['bank_code']
            );

            if (!$resolvedDetails || !isset($resolvedDetails['account_name'])) {
                return false;
            }

            // Normalize names for comparison (remove extra spaces, special chars, etc.)
            $submittedName = $this->normalizeName($data['account_name']);
            $resolvedName = $this->normalizeName($resolvedDetails['account_name']);

            // Verify account name matches (case insensitive)
            if (strcasecmp($submittedName, $resolvedName) !== 0) {
                Log::warning('Account name mismatch', [
                    'submitted' => $data['account_name'],
                    'resolved' => $resolvedDetails['account_name']
                ]);
                return false;
            }

            return true;

        } catch (Exception $e) {
            Log::error('Account verification failed: '.$e->getMessage(), [
                'account_number' => $data['account_number'],
                'bank_code' => $data['bank_code']
            ]);
            return false;
        }
    }

    /**
     * @param string $name
     * @return string
     */
    protected function normalizeName(string $name): string
    {
        // Remove extra whitespace
        $name = preg_replace('/\s+/', ' ', trim($name));

        // Remove special characters (keep letters, spaces, and common name characters)
        return preg_replace('/[^a-zA-Z\s\-.\']/', '', $name);
    }
}
