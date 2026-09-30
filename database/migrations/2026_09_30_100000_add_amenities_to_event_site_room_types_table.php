<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_site_room_types', function (Blueprint $table) {
            $table->string('amenities', 500)->nullable()->after('beds');
        });
    }

    public function down(): void
    {
        Schema::table('event_site_room_types', function (Blueprint $table) {
            $table->dropColumn('amenities');
        });
    }
};
