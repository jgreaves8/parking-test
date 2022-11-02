<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = ['car_registration', 'customer_name', 'booking_reference'];

    protected $appends = ['dates_booked'];

    /**
     * @return HasMany
     */
    public function bookingDates(): HasMany
    {
        return $this->hasMany(BookingDate::class);
    }

    /**
     * Return dates in human-readable format on Booking model
     * e.g. "dates" => ["02-01-2023", "03-01-2023"]
     *
     * @return array
     */
    public function getDatesBookedAttribute(): array
    {
        $bookingDates = $this->bookingDates()->get()->toArray();

        return array_map(function($dateObject){
            return Carbon::parse($dateObject['date'])->format('d-m-Y');
        }, $bookingDates);

    }

    /**
     * @param string $dateFrom
     * @param string $dateTo
     * @return bool
     */
    public static function bookingTooLong(string $dateFrom, string $dateTo): bool
    {
        if (self::diffDays($dateFrom, $dateTo) > config('parking.max_days_per_booking')) {
            return true;
        }

        return false;
    }

    /**
     * Calculate number of days between two d-m-Y dates
     *
     * @param string $dateFrom
     * @param string $dateTo
     * @return int
     */
    public static function diffDays(string $dateFrom, string $dateTo): int
    {
        return Carbon::parse($dateFrom)->diffInDays(Carbon::parse($dateTo));
    }

    public static function saveBooking(Array|Request $request)
    {
        if (is_array($request)) {
            $request = collect($request);
        }

        $booking = Booking::create([
            'car_registration' => $request->car_registration,
            'customer_name' => $request->customer_name,
        ]);

        BookingDate::saveDates($request->date_from, $request->date_to, $booking->id);

        return $booking;
    }

    public static function freeSpacesForDates(string $date_from, string $date_to)
    {
        $datesRange = CarbonPeriod::create($date_from, $date_to);

        $availabilitiesForDates = [];
        foreach($datesRange as $d) {
            $availabilitiesForDates[$d->format('d-m-Y')] = Booking::freeSpaceForDate($d);
        }

        return $availabilitiesForDates;
    }

    /**
     * @param string $date
     * @return int
     */
    public static function freeSpaceForDate(string $date): int
    {
        $maxOccupantsPerDay = config('parking.max_cars_per_day');
        $carsBookedOnDay = BookingDate::where('date', $date)->count();
        return $maxOccupantsPerDay - $carsBookedOnDay;
    }

    /**
     * @param int $spacesAvailable
     * @return string
     */
    public static function spacesAvailableText(int $spacesAvailable): string
    {
        $spacesText = ($spacesAvailable === 1) ? 'space' : 'spaces';
        return "$spacesAvailable $spacesText available";
    }

    public static function getPriceForDate(string $date)
    {
        $numericMonth = Carbon::parse($date)->month;
        return config("parking.pricing.$numericMonth");
    }

    /**
     * @param string $from
     * @param string $to
     * @param $format
     * @return array
     */
    public static function datesRangeArray(string $from, string $to, $format = 'd-m-Y'): array
    {
        $rangeOfRequestedDates = collect(CarbonPeriod::create($from, $to))->toArray();

        if (!$format) {
            return $rangeOfRequestedDates;
        }

        return array_map(function($date){
            return $date->format('d-m-Y');
        }, $rangeOfRequestedDates);

    }
}
