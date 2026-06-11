<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('image'); // путь к файлу
            $table->unsignedInteger('order')->default(0); // порядок в карусели
            $table->boolean('active')->default(true);
            $table->unsignedInteger('rotation_seconds')->default(0); // 0 = один баннер, >0 = интервал смены
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};