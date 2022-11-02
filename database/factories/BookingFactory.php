<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class BookingFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'booking_reference' => uniqid(false, false),
            'car_registration' => fake()->regexify('/[A-Z]{2}[0-9]{2} ?[A-Z]{3}/'),
            'customer_name' => fake()->name,
        ];
    }

    protected function dateFromHumanDate(string $date)
    {
        return Carbon::parse($date)->format('Y-m-d');
    }
}
