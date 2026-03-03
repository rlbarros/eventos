<?php

use App\Models\EventTrip;
use App\Models\Person;
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
        Schema::create('events_trips_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(EventTrip::class)->constrained();
            $table->foreignIdFor(Person::class)->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events_trips_participants', function (Blueprint $table) {
            $table->dropForeign(['event_trip_id']);
            $table->dropForeign(['person_id']);
        });
        Schema::dropIfExists('events_trips_participants');
    }
};
