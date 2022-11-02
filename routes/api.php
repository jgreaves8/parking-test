<?php

use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PriceCheckController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('bookings')->name('booking.')->group(function () {
    Route::get('/', [BookingController::class, 'index']); //PURELY FOR DEMO, would not be available obviously
    Route::post('/', [BookingController::class, 'store'])->name('store');
    Route::get('/{booking:booking_reference}', [BookingController::class, 'show'])->name('show');
    Route::put('/{booking:booking_reference}', [BookingController::class, 'update'])->name('update');
    Route::delete('/{booking:booking_reference}', [BookingController::class, 'destroy'])->name('delete');
});



Route::any('/availability', [AvailabilityController::class, 'index'])->name('check-availability');
Route::any('/price', [PriceCheckController::class, 'index'])->name('check-price');
