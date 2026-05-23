<?php

use App\Models\EventServiceParticipantConsumption;
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
        Schema::rename('events_services_participants_comsuption', 'events_services_participants_consumption');

        Schema::table('events_services_participants_consumption', function (Blueprint $table) {
            $table->dropColumn('payment_date');
            $table->renameColumn('amount', 'quantity');
        });

        Schema::create('events_services_participants_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(EventServiceParticipantConsumption::class)->name('consumption_id')->constrained();
            $table->date('payment_date')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events_services_participants_payments', function (Blueprint $table) {
            $table->dropForeign('events_services_participants_payments_consumption_id_foreign');
            $table->dropColumn('consumption_id');
        });
        Schema::dropIfExists('events_services_participants_payments');

        Schema::table('events_services_participants_consumption', function (Blueprint $table) {
            $table->date('payment_date')->nullable();
            $table->renameColumn('quantity', 'amount');
        });

        Schema::rename('events_services_participants_consumption', 'events_services_participants_comsuption');
    }
};
