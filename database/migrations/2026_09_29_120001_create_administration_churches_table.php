<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogo de igrejas da administração, entregue pelo data-sync (ADR-008). O `id` é o
 * `igrejas.id` de lá, o mesmo de `churches.administration_system_id`: é por aqui que o
 * eventos sabe a superintendência de cada igreja e que o cadastro de igrejas escolhe a
 * igreja correspondente na administração.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('administration_churches', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('name');
            $table->unsignedBigInteger('superintendence_id')->nullable()->index();
            $table->string('superintendence_name')->nullable();
            $table->boolean('active')->default(true);
            $table->dateTime('synced_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('administration_churches');
    }
};
