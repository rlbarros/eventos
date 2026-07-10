<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Timestamps na tabela de alocações/participantes para permitir a
 * sincronização incremental (filtro ?desde= na rota /participants-sync).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events_participants_allocations', function (Blueprint $table) {
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('events_participants_allocations', function (Blueprint $table) {
            $table->dropTimestamps();
        });
    }
};
