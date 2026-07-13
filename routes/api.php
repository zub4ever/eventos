<?php

use App\Modules\Booking\Http\Controllers\BookingController;
use App\Modules\Booking\Http\Controllers\PublicCalendarController;
use App\Modules\Payment\Http\Controllers\BookingPaymentController;
use App\Modules\Payment\Http\Controllers\PaymentWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/payments/{provider}', [PaymentWebhookController::class, 'store'])
    ->whereIn('provider', ['abacatepay', 'asaas']);

Route::prefix('public')->group(function (): void {
    Route::get('/calendar', [PublicCalendarController::class, 'index']);
});

Route::middleware('auth')->group(function (): void {
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);
    Route::post('/bookings/{booking}/payments', [BookingPaymentController::class, 'store']);
});