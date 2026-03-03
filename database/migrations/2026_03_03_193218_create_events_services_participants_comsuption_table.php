<?php

use App\Models\EventService;
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
        Schema::create('events_services_participants_comsuption', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(EventService::class)->constrained();
            $table->foreignIdFor(Person::class)->constrained();
            $table->date('paymanent_date')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events_services_participants_comsuption', function (Blueprint $table) {
            $table->dropForeign(['event_service_id']);
            $table->dropForeign(['person_id']);
        });
        Schema::dropIfExists('events_services_participants_comsuption');
    }
};
