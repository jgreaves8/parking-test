<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait Response {

    /**
     * @param $message
     * @return JsonResponse
     */
    public static function success($message = null): JsonResponse
    {
        return new JsonResponse([
            'message' => $message
        ], 200);
    }

    /**
     * @param $message
     * @param $code
     * @return JsonResponse
     */
    public static function error($message = null, $data = [], $code = 422): JsonResponse
    {
        $package = [
            'error' => $message
        ];

        if ($data) {
            $package = array_merge($package, $data);
        }

        return new JsonResponse($package, $code);
    }

    /**
     * @return JsonResponse
     */
    public static function errorBookingTooLong(): JsonResponse
    {
        return self::error('Booking exceeds maximum numbers of ' . config('parking.max_days_per_booking') . ' days', [], 422);
    }

}
