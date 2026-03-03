<?php

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
        Schema::create('states', function (Blueprint $table) {
            $table->id();
            $table->char('code', 2);
            $table->string('name', 191);
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->integer('ibge_id')->unsigned();
            $table->foreignIdFor(State::class)->constrained();
            $table->string('name', 191);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropForeign(['state_id']);
        });
        Schema::dropIfExists('cities');

        Schema::dropIfExists('states');
    }
};
