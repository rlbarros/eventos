<?php

use App\Models\Event;
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
        Schema::create('events_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Event::class)->constrained();
            $table->foreignIdFor(EventSiteRoomType::class)->constrained();
            $table->integer('batch')->nullable();
            $table->enum('category', ['Integral', 'Infantil'])->nullable(true);
            $table->decimal('fee', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events_fees', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
            $table->dropForeign(['event_site_room_type_id']);
        });
        Schema::dropIfExists('events_fees');
    }
};
