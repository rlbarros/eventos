<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // chave PIX para pagar a inscrição (mostrada no superapp) e quem recebe
        Schema::table('events', function (Blueprint $table) {
            $table->string('pix_key', 140)->nullable()->after('contact_phone');
            $table->string('pix_beneficiary', 200)->nullable()->after('pix_key');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['pix_key', 'pix_beneficiary']);
        });
    }
};
