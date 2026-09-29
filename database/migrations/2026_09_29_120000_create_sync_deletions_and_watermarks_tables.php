<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sincronização nos dois sentidos com a administração (via data-sync).
 *
 * `sync_deletions`: evento ou participação excluídos aqui. O data-sync só faz upsert, então a
 * exclusão viaja para a administração como `active = false` a partir desta tabela. Também serve
 * para o "último a alterar vence": uma participação que chega da administração com alteração
 * mais antiga que a exclusão daqui não é recriada.
 *
 * `sync_watermarks`: até onde já recebemos de cada origem (no relógio da origem), devolvido no
 * GET /sync para o data-sync pedir só o que veio depois.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_deletions', function (Blueprint $table) {
            $table->id();
            $table->string('model', 40);
            $table->unsignedBigInteger('record_id');
            $table->unsignedBigInteger('event_id')->nullable();
            $table->string('cpf', 14)->nullable();
            $table->timestamp('deleted_at');

            $table->index(['model', 'deleted_at']);
            $table->index(['model', 'record_id']);
            $table->index(['model', 'event_id']);
        });

        Schema::create('sync_watermarks', function (Blueprint $table) {
            $table->id();
            $table->string('source', 40);
            $table->string('model', 60);
            $table->timestamp('last_changed_at');

            $table->unique(['source', 'model']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_watermarks');
        Schema::dropIfExists('sync_deletions');
    }
};
