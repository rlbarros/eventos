<?php

use App\Models\EventDriver;
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
        Schema::create('events_trips', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(EventDriver::class)->constrained();
            $table->string('from', 200);
            $table->date('start_date');
            $table->string('to', 200);
            $table->date('end_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events_trips', function (Blueprint $table) {
            $table->dropForeign(['event_driver_id']);
        });
        Schema::dropIfExists('events_trips');
    }
};
