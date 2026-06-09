<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('preparation_time')->default(0)->after('price'); // в минутах
            $table->unsignedInteger('stock')->nullable()->after('preparation_time');
            $table->boolean('unlimited')->default(false)->after('stock');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['preparation_time', 'stock', 'unlimited']);
        });
    }
};