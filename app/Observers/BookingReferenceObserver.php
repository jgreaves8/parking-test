<?php

namespace App\Observers;

use App\Models\Booking;

class BookingReferenceObserver
{

    public function creating(Booking $booking)
    {
        $this->assignUniqueBookingReference($booking);
    }

    /**
     * @param Booking $booking
     * @return void
     */
    protected function assignUniqueBookingReference(Booking $booking): void
    {
        $bookingReference = uniqid(null, false);

        //unlikely for collision to happen but why not
        if (Booking::where('booking_reference', $bookingReference)->exists()) {
            $this->generateBookingReference($booking);
        }

        $booking->booking_reference = $bookingReference;
    }

}
