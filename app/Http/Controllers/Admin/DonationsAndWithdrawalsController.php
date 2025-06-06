<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\WithdrawalApprovedConfirmation;
use App\Mail\WithdrawalRejectedConfirmation;
use App\Models\Donation;
use App\Models\Withdrawal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;
use Mockery\Exception;
use Throwable;

class DonationsAndWithdrawalsController extends Controller
{
    public function index()
    {
        $donations = Donation::query()
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

        return Inertia::render('Admin/Donations/Index', [
            'donations' => $donations,
            'filters' => request()->all('search')
        ]);
    }

    /**
     * Display withdrawal requests with sorting and filtering
     *
     * @param Request $request
     * @return Response
     */
    public function withdrawals(Request $request)
    {
        $request->validate([
            'sort' => 'nullable|in:approved,pending,rejected',
            'order' => 'nullable|in:asc,desc'
        ]);

        $withdrawals = Withdrawal::with([
            'user:id,first_name,last_name,avatar',
            'withdrawal_settings:id,account_number,bank_name'
        ])
            ->select('id', 'status', 'amount', 'created_at', 'user_id', 'withdrawal_settings_id')
            ->when($request->sort, function ($query, $sort) {
                return $query->where('status', $sort);
            })
            ->orderBy('created_at', $request->order ?? 'desc')
            ->paginate(8)
            ->withQueryString();

        return Inertia::render('Admin/Withdrawals/Index', [
            'withdrawals' => $withdrawals,
            'filters' => [
                'sort' => $request->sort ?? '',
                'order' => $request->order ?? 'desc'
            ]
        ]);
    }

    /**
     * Approve a withdrawal request
     *
     * @param Withdrawal $withdrawal
     * @return RedirectResponse
     * @throws Throwable
     */
    public function approveWithdrawal(Withdrawal $withdrawal)
    {
        // Check if withdrawal is already approved
        if ($withdrawal->status === 'approved') {
            return redirect()->back()->with('error', 'This withdrawal has already been approved.');
        }

        $user = $withdrawal->user;

        DB::beginTransaction();

        try {
            // Update withdrawal status
            $withdrawal->update([
                'status' => 'approved',
            ]);

            // Send approval notification
            if (config('settings.email_notification')) {
                Mail::mailer(config('settings.email_provider'))
                    ->to($user->email)
                    ->send(new WithdrawalApprovedConfirmation($user, $withdrawal));
            }

            DB::commit();

            return redirect()->back()->with('success', 'Withdrawal approved successfully.');
        } catch (Exception $exception) {
            DB::rollBack();

            Log::error('Failed to approve withdrawal: '.$exception->getMessage(), [
                'withdrawal_id' => $withdrawal->id,
                'user_id' => $user->id,
            ]);

            return redirect()->back()->with('error', 'Failed to approve withdrawal. Please try again.');
        }
    }

    /**
     * Reject a withdrawal request and refund the user
     *
     * @param Withdrawal $withdrawal
     * @return RedirectResponse
     * @throws Throwable
     */
    public function rejectWithdrawal(Withdrawal $withdrawal)
    {
        // Check withdrawal status
        if ($withdrawal->status === 'rejected') {
            return redirect()->back()->with('error', 'This withdrawal has already been rejected.');
        }

        if ($withdrawal->status === 'approved') {
            return redirect()->back()->with('error', 'Approved withdrawals cannot be rejected.');
        }

        $user = $withdrawal->user;

        DB::beginTransaction();

        try {

            // Update withdrawal status
            $withdrawal->update([
                'status' => 'rejected'
            ]);

            // Refund user
            $user->increment('balance', $withdrawal->amount);

            // Send rejection notification
            if (config('settings.email_notification')) {
                Mail::mailer(config('settings.email_provider'))
                    ->to($user->email)
                    ->send(new WithdrawalRejectedConfirmation($user, $withdrawal));
            }

            DB::commit();

            return redirect()->back()
                ->with('success', 'Withdrawal rejected and funds returned to user balance.');

        } catch (Exception $exception) {
            DB::rollBack();

            Log::error('Failed to reject withdrawal: '.$exception->getMessage(), [
                'withdrawal_id' => $withdrawal->id,
                'user_id' => $user->id,
                'amount' => $withdrawal->amount,
            ]);

            return redirect()->back()
                ->with('error', 'Failed to process withdrawal rejection. Please try again.');
        }
    }
}
