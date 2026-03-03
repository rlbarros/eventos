<?php

use App\Models\Event;
use App\Models\EventSiteRoom;
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
        Schema::create('events_paticipants_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Event::class)->constrained();
            $table->foreignIdFor(Person::class)->constrained();
            $table->foreignIdFor(EventSiteRoom::class)->nullable()->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events_paticipants_allocations', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
            $table->dropForeign(['person_id']);
        });
        Schema::dropIfExists('events_paticipants_allocations');
    }
};
