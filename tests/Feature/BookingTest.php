<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Booking;
use App\Models\BookingDate;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test get correct errors when attempting to create empty booking
     *
     * @return void
     */
    public function test_cannot_create_booking_without_params()
    {
        $response = $this->post(route('booking.store'));
        $response->assertStatus(422);
        $response->assertInvalid([
            'date_from', 'date_to', 'car_registration', 'customer_name'
        ]);
    }

    public function test_can_create_booking_with_params()
    {
        $dateFrom = '01-01-2022';
        $dateTo = '03-01-2022';
        $carRegistration = 'AA11 AAA';
        $customerName = 'Jonathan Greaves';

        $response = $this->post(route('booking.store'), [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'car_registration' => $carRegistration,
            'customer_name' => $customerName
        ]);
        $response->assertStatus(201);
        $response->assertJsonPath('car_registration', $carRegistration);
        $response->assertJsonPath('customer_name', $customerName);
        $response->assertSee('booking_reference');
    }

    public function test_can_amend_booking_where_no_restrictions(): void
    {
        $dateFrom = '01-01-2022';
        $dateTo = '03-01-2022';
        $carRegistration = 'AA11 AAA';
        $customerName = 'Jonathan Greaves';

        $response = $this->post('/api/bookings', [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'car_registration' => $carRegistration,
            'customer_name' => $customerName
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('car_registration', $carRegistration);
        $response->assertJsonPath('customer_name', $customerName);
        $response->assertSee('booking_reference');

        //assert can see correct all dates on response, not just FROM and TO
        foreach(CarbonPeriod::create($dateFrom, $dateTo) as $d) {
            $response->assertSee($d->format('d-m-Y'));
        }

        $bookingReference = $response['booking_reference'];

        $updatedDateFrom = '02-02-2022';
        $updatedDateTo = '05-02-2022';

        $updateResponse = $this->put('/api/bookings/' . $bookingReference, [
            'date_from' => $updatedDateFrom,
            'date_to' => $updatedDateTo
        ]);

        $updateResponse->assertStatus(200);

        //assert can see updated dates
        foreach(CarbonPeriod::create($updatedDateFrom, $updatedDateTo) as $d) {
            $updateResponse->assertSee($d->format('d-m-Y'));
        }
    }

    public function test_can_amend_booking_where_currently_full_without_taking_current_booking_into_account(): void
    {
        $dateFrom = '01-01-2022';
        $dateTo = '05-01-2022';

        //create booked-up dates, 10 cars each day
        foreach (range(1, 10) as $r) {
            $carRegistration = fake()->regexify('/[A-Z]{2}[0-9]{2} ?[A-Z]{3}/');
            $customerName = fake()->name . ' ' . $r;

            $this->post('/api/bookings', [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'car_registration' => $carRegistration,
                'customer_name' => $customerName
            ]);
        }

        $this->assertEquals(50, BookingDate::count());

        //send another create, just to confirm it won't go through for those dates
        $response = $this->post(route('booking.store'), [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'car_registration' => 'AA25 AYU',
            'customer_name' => 'Jonathan Greaves'
        ]);

        $this->assertEquals(50, BookingDate::count());

        //dates already booked up, should fail
        $response->assertStatus(422);

        //check all previous dates on booking
        foreach(CarbonPeriod::create($dateFrom, $dateTo) as $d) {
            $response->assertSee($d->format('d-m-Y'));
        }

        $lastBookingReference = Booking::first()->booking_reference;

        $response = $this->put(route('booking.update', $lastBookingReference), [
            'date_from' => '02-01-2022',
            'date_to' => '07-01-2022',
        ]);

        $response->assertStatus(200);

        //assert can see updated dates
        foreach(CarbonPeriod::create('02-01-2022', '07-01-2022') as $d) {
            $response->assertSee($d->format('d-m-Y'));
        }
    }

}
