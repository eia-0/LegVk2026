<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            if (!Schema::hasColumn('banners', 'interval')) {
                $table->unsignedInteger('interval')->default(0)->after('image');
            }
            if (!Schema::hasColumn('banners', 'order')) {
                $table->unsignedInteger('order')->default(0)->after('interval');
            }
            if (!Schema::hasColumn('banners', 'active')) {
                $table->boolean('active')->default(true)->after('order');
            }
        });
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn(['interval', 'order', 'active']);
        });
    }
};