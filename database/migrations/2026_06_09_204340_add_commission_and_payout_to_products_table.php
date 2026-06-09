<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedTinyInteger('commission_percent')->nullable()->after('partner_id');
            $table->timestamp('payout_reset_at')->nullable()->after('commission_percent');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['commission_percent', 'payout_reset_at']);
        });
    }
};