<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->decimal('charged_amount', 10, 2)->nullable()->after('returned_at');
            $table->decimal('late_fee_amount', 10, 2)->nullable()->after('charged_amount');
            $table->boolean('volume_discount_applied')->nullable()->after('late_fee_amount');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'charged_amount',
                'late_fee_amount',
                'volume_discount_applied',
            ]);
        });
    }
};
