<?php

namespace App\Observers;

use App\Models\BookingDate;

class BookingDeletionObserver
{

    /**
     * I could do this with foreign keys and cascading, but it doesn't seem to work nicely on sqlite
     *
     * @param $model
     * @return void
     */
    public function deleted($model)
    {

        BookingDate::where('booking_id', $model->id)->delete();
    }

}
