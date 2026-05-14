<?php

use App\Models\Event;
use App\Models\EventBatch;
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
        Schema::create('events_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Event::class)->constrained();
            $table->integer('batch');
            $table->date('start_date');
            $table->date('end_date');
        });

        Schema::table('events_fees', function (Blueprint $table) {
            $table->dropColumn('batch');
            $table->foreignIdFor(EventBatch::class)->after('event_site_room_type_id')->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events_fees', function (Blueprint $table) {
            $table->dropForeign(['event_batch_id']);
            $table->dropColumn('event_batch_id');
            $table->integer('batch')->after('event_site_room_type_id')->nullable();
        });

        Schema::table('events_batches', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
        });
        Schema::dropIfExists('events_batches');
    }
};
