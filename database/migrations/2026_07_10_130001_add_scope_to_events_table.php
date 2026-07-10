<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Abrangência do evento. É exportada para a administração (administracao-api)
 * onde alimenta a regra dos ministros nacionais (não podem faltar a 3 eventos
 * nacionais). 'nacional' | 'superintendencia' | 'igreja'.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->enum('scope', ['nacional', 'superintendencia', 'igreja'])
                ->default('igreja')
                ->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('scope');
        });
    }
};
