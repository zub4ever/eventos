<?php

use App\Modules\Booking\Http\Controllers\BookingController;
use App\Modules\Payment\Http\Controllers\BookingPaymentController;
use App\Modules\PublicPortal\Http\Controllers\PortalAuthController;
use App\Modules\PublicPortal\Http\Controllers\PublicPortalController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [PublicPortalController::class, 'show'])->name('home');

Route::prefix('portal/auth')->group(function (): void {
    Route::post('/register', [PortalAuthController::class, 'register'])->name('portal.auth.register');
    Route::post('/login', [PortalAuthController::class, 'login'])->name('portal.auth.login');
    Route::post('/logout', [PortalAuthController::class, 'logout'])->middleware('auth')->name('portal.auth.logout');
});

Route::middleware('auth')->prefix('portal')->group(function (): void {
    Route::post('/bookings', [BookingController::class, 'store'])->name('portal.bookings.store');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('portal.bookings.show');
    Route::post('/bookings/{booking}/payments', [BookingPaymentController::class, 'store'])->name('portal.bookings.payments.store');
});

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => config('app.name'),
    ]);
})->name('health');
