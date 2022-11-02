<?php

namespace App\Http\Controllers;

use App\Http\Requests\AvailabilityCheckRequest;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\BookingDate;
use App\Traits\Response;
use App\Traits\Responses;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    use Response;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Booking::all();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreBookingRequest $request)
    {
        if (Booking::bookingTooLong($request->date_from, $request->date_to)) {
            return Response::errorBookingTooLong();
        }

        $freeSpacesForDates = Booking::freeSpacesForDates($request->date_from, $request->date_to);

        //array of booked-up dates within range of request
        $bookedUpDates = array_filter($freeSpacesForDates, function($spacesAvailableForDate){
            return !$spacesAvailableForDate;
        });

        //if any dates have zero free spaces, reject the request
        if ($bookedUpDates) {
            return Response::error('Not all dates available', ['Unavailable dates' => array_keys($bookedUpDates)]);
        }

        return Booking::saveBooking($request);
    }

    /**
     * Display the specified resource.
     *
     * @param Booking $booking
     * @return JsonResponse
     */
    public function show(Booking $booking): JsonResponse
    {
        return $booking;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function update(AvailabilityCheckRequest $request, Booking $booking)
    {
        if (Booking::bookingTooLong($request->date_from, $request->date_to)) {
            return Response::errorBookingTooLong();
        }

        $existingDatesForBooking = $booking->datesBooked;
        $freeSpacesForDates = Booking::freeSpacesForDates($request->date_from, $request->date_to);

        //need to exclude current booking dates from availability check
        foreach($freeSpacesForDates as $k => $date) {
            if (array_key_exists($date, $existingDatesForBooking)) {
                $freeSpacesForDates[$k]--;
            }
        }

        $alreadyFullDates = array_filter($freeSpacesForDates, function($d){
             return !$d;
        });

        if ($alreadyFullDates) {
            return Response::error('Cannot amend booking, certain dates are already full', ['already_full' => $alreadyFullDates], 422);
        }

        BookingDate::where('booking_id', $booking->id)->delete();
        BookingDate::saveDates($request->date_from, $request->date_to, $booking->id);

        $booking->refresh();
        return $booking;
    }

    /**
     * Remove the specified resource from storage.
     * @param string $bookingReference
     * @return JsonResponse|void
     */
    public function destroy(string $bookingReference)
    {
        $booking = Booking::where('booking_reference', $bookingReference)->first();

        if (!$booking) {
            return Response::error('Booking cannot be found', [], 404);
        }

        if ($booking->delete()) {
            return Response::success('Booking ' . $bookingReference . ' cancelled', 204);
        }

    }


}

/**
 *              Customer able to check if available car parking space for certain dates
 *              Customer can check pricing for given dates
 *              Customer can create booking for given dates
 *              Customer can cancel given booking
 * Customer can amend given booking
 */

/**
 * @todo more tests, check everything
 * @todo add update route, check if they removed their own
 * @todo get tests up and running with sqlite
 * @todo cascade on delete
 */
