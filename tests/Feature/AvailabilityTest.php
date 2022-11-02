<?php

namespace Tests\Feature;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test get correct errors when checking availability for too many days
     * (more than config('parking.max_days_per_booking')
     *
     * @return void
     */
    public function test_cannot_check_availability_if_number_of_days_too_high()
    {
        $response = $this->post(route('check-availability'), [
            'date_from' => '01-01-2023',
            'date_to' => '01-02-2023',
        ]);
        $response->assertJsonPath('error', 'Booking exceeds maximum numbers of ' . config('parking.max_days_per_booking') . ' days');
    }

    /**
     * Test no errors when attempting to create empty booking
     *
     * @return void
     */
    public function test_can_check_availability_if_number_of_days_in_range()
    {
        $response = $this->post(route('check-availability'), [
            'date_from' => '01-01-2023',
            'date_to' => '02-01-2023',
        ]);
        $response->assertStatus(200);
    }

    /**
     * Test no errors when attempting to create empty booking
     *
     * @return void
     */
    //@todo complete
    public function test_cannot_book_for_day_that_is_already_full()
    {
//        foreach(range(10, 25) as $range) {
//
//            Booking::factory([
//                'date_from' => Carbon::parse("{$range}-01-2023"),
//                'date_to' => Carbon::parse("{$range}-01-2023"),
//            ]);
//        }
        $response = $this->post(route('check-availability'), [
            'date_from' => '01-01-2023',
            'date_to' => '01-01-2023',
        ]);
        $response->assertStatus(200);
    }


}
