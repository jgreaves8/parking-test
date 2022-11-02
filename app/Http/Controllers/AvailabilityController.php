<?php

namespace App\Http\Controllers;

use App\Http\Requests\AvailabilityCheckRequest;
use App\Models\Booking;
use App\Traits\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    /**
     * @param AvailabilityCheckRequest $request
     * @return JsonResponse
     */
    public function index(AvailabilityCheckRequest $request): JsonResponse
    {
        if (Booking::bookingTooLong($request->date_from, $request->date_to)) {
            return Response::errorBookingTooLong();
        }

        $freeSpacesForDates = Booking::freeSpacesForDates($request->date_from, $request->date_to);

        return Response::success(array_map(static function($d){
            return ($d === 1) ? "$d space available" : "$d spaces available";
        }, $freeSpacesForDates));
    }
}
