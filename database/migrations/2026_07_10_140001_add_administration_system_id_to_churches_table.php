<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vínculo da igreja com o cadastro da administração (administracao-api): guarda
 * o id da igreja lá (igrejas.id). Permite que a sincronização de eventos leve o
 * `igreja_id` para a administração e viabilize o escopo por igreja.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('churches', function (Blueprint $table) {
            $table->unsignedBigInteger('administration_system_id')->nullable()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('churches', function (Blueprint $table) {
            $table->dropColumn('administration_system_id');
        });
    }
};
