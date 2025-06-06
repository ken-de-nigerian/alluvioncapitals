<?php

namespace App\Http\Controllers\GeoLocation;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class LocationController extends Controller
{
    const CACHE_DURATION = 43200; // 30 days in minutes

    /**
     * Handle location requests with caching and minimal data
     */
    private function handleLocationRequest(string $filename, array $filters = []): JsonResponse
    {
        $cacheKey = "locations.$filename." . md5(json_encode($filters));

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($filename, $filters) {
            try {
                $path = "locations/$filename.json";

                if (!Storage::disk('public')->exists($path)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Location data not found'
                    ], Response::HTTP_NOT_FOUND);
                }

                $data = json_decode(Storage::disk('public')->get($path), true);

                // Apply filters if provided
                if (!empty($filters)) {
                    $data = array_values(array_filter($data, function ($item) use ($filters) {
                        foreach ($filters as $key => $value) {
                            if (!isset($item[$key]) || $item[$key] != $value) {
                                return false;
                            }
                        }
                        return true;
                    }));
                }

                // Return only 'id' and 'name'
                $data = array_map(function ($item) {
                    return [
                        'id' => $item['id'] ?? null,
                        'name' => $item['name'] ?? null,
                    ];
                }, $data);

                return response()->json([
                    'status' => 'success',
                    'data' => $data,
                    'cached' => false
                ])->setEncodingOptions(JSON_UNESCAPED_UNICODE);

            } catch (Exception $e) {
                report($e);
                return response()->json([
                    'status' => 'error',
                    'message' => 'An unexpected error occurred'
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        });
    }

    /**
     * Get all countries (id and name only)
     */
    public function countries(): JsonResponse
    {
        return $this->handleLocationRequest('countries');
    }

    /**
     * Get states for a country (id and name only)
     */
    public function states(): JsonResponse
    {
        $countryId = request()->input('country');

        if (!$countryId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Country ID is required'
            ], Response::HTTP_BAD_REQUEST);
        }

        return $this->handleLocationRequest('states', [
            'country_id' => (int)$countryId
        ]);
    }
}
