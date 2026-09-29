<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // contato principal do evento: quem recebe as inscrições
        Schema::table('events', function (Blueprint $table) {
            $table->string('contact_name', 200)->nullable()->after('name');
            $table->string('contact_phone', 20)->nullable()->after('contact_name');
        });

        // transportador (empresa/responsável pelo veículo), separado do motorista
        Schema::table('events_trips', function (Blueprint $table) {
            $table->string('transporter_name', 200)->nullable()->after('event_driver_id');
            $table->string('transporter_phone', 20)->nullable()->after('transporter_name');
        });
    }

    public function down(): void
    {
        Schema::table('events_trips', function (Blueprint $table) {
            $table->dropColumn(['transporter_name', 'transporter_phone']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['contact_name', 'contact_phone']);
        });
    }
};
