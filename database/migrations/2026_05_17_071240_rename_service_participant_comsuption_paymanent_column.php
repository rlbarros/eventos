<?php

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
        Schema::table('events_services_participants_comsuption', function (Blueprint $table) {
            $table->renameColumn('paymanent_date', 'payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events_services_participants_comsuption', function (Blueprint $table) {
            $table->renameColumn('payment_date', 'paymanent_date');
        });
    }
};
