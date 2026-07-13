<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')
                ->constrained('tenants')
                ->cascadeOnDelete();
            $table->foreignUuid('user_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->date('event_date');
            $table->enum('period_type', ['regular', 'extended']);
            $table->decimal('total_price', 10, 2);
            $table->enum('status', ['pending', 'confirmed', 'canceled']);
            $table->timestamp('payment_expires_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'event_date']);
            $table->index('tenant_id');
            $table->index('status');
            $table->index('event_date');
            $table->index('payment_expires_at');
            $table->index(['status', 'payment_expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
