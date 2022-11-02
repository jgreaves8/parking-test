<?php

namespace App\Http\Controllers;

use App\Http\Requests\AvailabilityCheckRequest;
use App\Models\Booking;
use App\Traits\Response;
use Illuminate\Http\JsonResponse;

class PriceCheckController extends Controller
{
    use Response;

    public function index(AvailabilityCheckRequest $request){

        if (Booking::bookingTooLong($request->date_from, $request->date_to)) {
            return Response::errorBookingTooLong();
        }

        $freeSpacesForDates = Booking::freeSpacesForDates($request->date_from, $request->date_to);

        $prices = [];
        $totalPrice = 0;
        foreach ($freeSpacesForDates as $date => $freeSpacesForDate) {
            if (!$freeSpacesForDate) {
                $prices[$date] = "Unavailable";
                continue;
            }
            $priceForDate = Booking::getPriceForDate($date);
            $totalPrice += $priceForDate;
            $prices[$date] = '£' . $priceForDate;
        }

        return new JsonResponse([
            'dates' => $prices,
            'total_price' => '£' . $totalPrice
        ]);

    }

}
