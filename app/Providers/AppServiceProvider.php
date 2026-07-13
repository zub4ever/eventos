<?php

namespace App\Providers;

use App\Models\Booking;
use App\Modules\Booking\Events\BookingCanceled;
use App\Modules\Booking\Events\BookingConfirmed;
use App\Modules\Booking\Listeners\LogBookingCanceled;
use App\Modules\Booking\Listeners\LogBookingConfirmed;
use App\Modules\Payment\Factories\PaymentGatewayFactory;
use App\Modules\Payment\Gateways\AbacatePayGateway;
use App\Modules\Payment\Gateways\AsaasGateway;
use App\Modules\Payment\Events\PaymentReceived;
use App\Modules\Payment\Listeners\LogPaymentReceived;
use App\Policies\BookingPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AbacatePayGateway::class);
        $this->app->singleton(AsaasGateway::class);
        $this->app->singleton(PaymentGatewayFactory::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Booking::class, BookingPolicy::class);

        Event::listen(BookingConfirmed::class, LogBookingConfirmed::class);
        Event::listen(BookingCanceled::class, LogBookingCanceled::class);
        Event::listen(PaymentReceived::class, LogPaymentReceived::class);
    }
}
