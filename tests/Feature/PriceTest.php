<?php

namespace Tests\Feature;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PriceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_pricing_works_for_same_month(): void
    {
        $response = $this->post(route('check-price'), [
            'date_from' => '01-01-2023',
            'date_to' => '02-01-2023',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('total_price', '£' . 2 * config('parking.pricing.1'));
    }

    /**
     *
     */
    /**
     * @return void
     */
    public function test_pricing_works_for_dates_crossing_months(): void
    {
        $response = $this->post(route('check-price'), [
            'date_from' => '30-08-2023',
            'date_to' => '04-09-2023',
        ]);

        $response->assertStatus(200);
        $expectedPrice = 2 * config('parking.pricing.8') + 4 * config('parking.pricing.9');
        $response->assertJsonPath('total_price', '£' . $expectedPrice);
    }
}
