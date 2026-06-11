<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Удаляем старые текстовые поля курьера, если они есть
            if (Schema::hasColumn('orders', 'courier_name')) {
                $table->dropColumn('courier_name');
            }
            if (Schema::hasColumn('orders', 'courier_phone')) {
                $table->dropColumn('courier_phone');
            }
            // Добавляем courier_id только если его ещё нет
            if (!Schema::hasColumn('orders', 'courier_id')) {
                $table->foreignId('courier_id')->nullable()->constrained('users')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'courier_id')) {
                $table->dropForeign(['courier_id']);
                $table->dropColumn('courier_id');
            }
        });
    }
};