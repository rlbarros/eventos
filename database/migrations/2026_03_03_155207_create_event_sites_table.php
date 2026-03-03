<?php

use App\Models\City;
use App\Models\State;
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
        Schema::create('event_sites', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('zip_code', 9)->nullable();
            $table->foreignIdFor(State::class)->constrained();
            $table->foreignIdFor(City::class)->constrained();
            $table->string('address');
            $table->string('number', 20)->nullable();
            $table->string('complement', 191)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_sites', function (Blueprint $table) {
            $table->dropForeign(['state_id']);
            $table->dropForeign(['city_id']);
        });
        Schema::dropIfExists('event_sites');
    }
};
