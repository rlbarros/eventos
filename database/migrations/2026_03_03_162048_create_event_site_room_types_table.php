<?php

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
        Schema::create('event_site_room_types', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(EventSite::class)->constrained();
            $table->string('name', 200);
            $table->enum('type', ['Alojamento', 'Apartamento', 'Hotel', 'Redário'])->nullable(true);
            $table->integer('beds')->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_site_room_types', function (Blueprint $table) {
            $table->dropForeign(['event_site_id']);
        });
        Schema::dropIfExists('event_site_room_types');
    }
};
