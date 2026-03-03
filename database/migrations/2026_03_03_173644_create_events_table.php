<?php

use App\Models\Church;
use App\Models\EventSite;
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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->date('start_date');
            $table->date('end_date');
            $table->foreignIdFor(Church::class)->constrained();
            $table->foreignIdFor(EventSite::class)->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['church_id']);
            $table->dropForeign(['event_site_id']);
        });
        Schema::dropIfExists('events');
    }
};
