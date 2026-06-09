<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_settings', function (Blueprint $table) {
            $table->decimal('delivery_cost', 8, 2)->default(0)->after('delivery_enabled');
            $table->decimal('free_delivery_from', 8, 2)->nullable()->after('delivery_cost');
        });
    }

    public function down(): void
    {
        Schema::table('shop_settings', function (Blueprint $table) {
            $table->dropColumn(['delivery_cost', 'free_delivery_from']);
        });
    }
};