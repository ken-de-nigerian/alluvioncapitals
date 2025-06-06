<?php

use App\Http\Controllers\GeoLocation\LocationController;
use App\Models\Donation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Countries Api
Route::get('/countries', [LocationController::class, 'countries']);

// States Api
Route::get('/states', [LocationController::class, 'states']);

// Donations Api
Route::get('/donations-chart', function(Request $request) {
    sleep(1);

    $period = $request->input('period', 'current_month');

    $query = Donation::select(
        DB::raw('DATE(created_at) as date'),
        DB::raw('SUM(CASE WHEN status = "approved" THEN amount ELSE 0 END) as approved'),
        DB::raw('SUM(CASE WHEN status = "pending" THEN amount ELSE 0 END) as pending'),
        DB::raw('SUM(CASE WHEN status = "rejected" THEN amount ELSE 0 END) as rejected')
    )
        ->groupBy('date')
        ->orderBy('date');

    // Apply date filters
    $now = now();
    switch ($period) {
        case 'last_month':
            $query->whereBetween('created_at', [
                $now->clone()->subMonth()->startOfMonth(),
                $now->clone()->subMonth()->endOfMonth()
            ]);
            break;
        case 'last_3_months':
            $query->whereBetween('created_at', [
                $now->clone()->subMonths(3)->startOfMonth(),
                $now->clone()->endOfMonth()
            ]);
            break;
        case 'last_6_months':
            $query->whereBetween('created_at', [
                $now->clone()->subMonths(6)->startOfMonth(),
                $now->clone()->endOfMonth()
            ]);
            break;
        case 'last_year':
            $query->whereBetween('created_at', [
                $now->clone()->subYear()->startOfYear(),
                $now->clone()->endOfYear()
            ]);
            break;
        default: // current_month
            $query->whereBetween('created_at', [
                $now->clone()->startOfMonth(),
                $now->clone()->endOfMonth()
            ]);
    }

    $donations = $query->get();

    // Format data for chart
    $labels = [];
    $approved = [];
    $pending = [];
    $rejected = [];

    foreach ($donations as $donation) {
        $date = Carbon::parse($donation->date);
        $labels[] = $date->format('d M');
        $approved[] = (float) $donation->approved;
        $pending[] = (float) $donation->pending;
        $rejected[] = (float) $donation->rejected;
    }

    return response()->json([
        'labels' => $labels,
        'datasets' => [
            [
                'label' => 'Approved',
                'data' => $approved,
            ],
            [
                'label' => 'Pending',
                'data' => $pending,
            ],
            [
                'label' => 'Failed',
                'data' => $rejected,
            ]
        ]
    ]);
})->name('donations.chart');
