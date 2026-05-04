<?php

use App\Models\EventSiteRoomType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events_participants_allocations', function (Blueprint $table) {
            $table->foreignIdFor(EventSiteRoomType::class)->after('person_id')->nullable()->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events_participants_allocations', function (Blueprint $table) {
            $table->dropColumn('event_site_room_type_id');
        });
    }
};
