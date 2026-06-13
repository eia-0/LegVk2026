<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('show_in_catalog')->default(true)->after('parent_id');
            $table->boolean('show_in_ready_eat')->default(false)->after('show_in_catalog');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['show_in_catalog', 'show_in_ready_eat']);
        });
    }
};