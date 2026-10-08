<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->date('pickup_date');
            $table->date('return_date');
            $table->timestamp('returned_at')->nullable();
            $table->timestamps();

            $table->index(['machine_id', 'pickup_date', 'return_date']);
            $table->index(['pickup_date', 'return_date']);
            $table->index(['customer_id', 'pickup_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
