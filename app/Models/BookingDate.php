<?php

namespace App\Models;

use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingDate extends Model
{
    use HasFactory;

    protected $fillable = ['booking_id', 'date'];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public static function saveDates(string $date_from, string $date_to, int $bookingId)
    {
        $datesRange = CarbonPeriod::create($date_from, $date_to);

        foreach($datesRange as $date) {
            BookingDate::create([
                'booking_id' => $bookingId,
                'date' => $date
            ]);
        }
    }
}
