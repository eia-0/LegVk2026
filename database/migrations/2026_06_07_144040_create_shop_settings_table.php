<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('delivery_enabled')->default(true);
            $table->text('opening_hours')->nullable(); // JSON или просто текст
            $table->timestamps();
        });

        // Вставляем дефолтную запись
        \DB::table('shop_settings')->insert([
            'delivery_enabled' => true,
            'opening_hours' => 'Пн-Вс: 12:00-23:00',
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_settings');
    }
};