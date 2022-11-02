<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\BookingDate;
use Carbon\CarbonPeriod;
use Tests\TestCase;

class BookingsTest extends TestCase
{
    public function test_dates_also_deleted_when_deleting_booking()
    {
        $this->assertEquals(0, Booking::count());
        $this->assertEquals(0, BookingDate::count());

        $booking = Booking::factory()->create();
        foreach(CarbonPeriod::create('01-01-2023', '05-01-2023') as $date) {
            BookingDate::create([
                'booking_id' => $booking->id,
                'date' => $date
            ]);
        }

        $this->assertEquals(1, Booking::count());
        $this->assertEquals(5, BookingDate::count());

        Booking::destroy($booking->id);
        $this->assertEquals(0, Booking::count());
        $this->assertEquals(0, BookingDate::count());
    }
}
