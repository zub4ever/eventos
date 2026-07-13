<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;
use App\Modules\Tenancy\Support\TenantContext;

class BookingPolicy
{
    public function create(User $user): bool
    {
        $tenantId = app(TenantContext::class)->id();

        return $tenantId !== null && (string) $user->tenant_id === (string) $tenantId;
    }

    public function view(User $user, Booking $booking): bool
    {
        return (string) $user->tenant_id === (string) $booking->tenant_id;
    }

    public function cancel(User $user, Booking $booking): bool
    {
        return $this->view($user, $booking);
    }

    public function pay(User $user, Booking $booking): bool
    {
        return $this->view($user, $booking);
    }
}