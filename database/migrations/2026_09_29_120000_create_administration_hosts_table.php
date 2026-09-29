<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Réplica dos anfitriões da administração (administracao-api), entregue pelo data-sync
 * (ADR-008). Só quem tem um registro ativo aqui pode criar conta, e a jurisdição do anfitrião
 * limita os eventos que ele manuseia. Um registro por usuário e jurisdição; revogação chega
 * como `active = false`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('administration_hosts', function (Blueprint $table) {
            $table->id();
            // id do registro na administração: "{usuario}:nacional", "{usuario}:superintendencia:{id}"...
            $table->string('grant_key', 80)->unique();
            $table->unsignedBigInteger('administration_user_id')->index();
            $table->string('name');
            $table->string('email')->index();
            $table->enum('level', ['nacional', 'superintendencia', 'igreja']);
            $table->unsignedBigInteger('administration_superintendence_id')->nullable();
            $table->unsignedBigInteger('administration_church_id')->nullable();
            $table->boolean('active')->default(true);
            // carimbo da administração (sincronizado_em), guardado como veio: é o `desde` do próximo delta
            $table->dateTime('synced_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            // usuário da administração (usuarios.id) de quem a conta é; nulo nas contas antigas
            $table->unsignedBigInteger('administration_user_id')->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['administration_user_id']);
            $table->dropColumn('administration_user_id');
        });
        Schema::dropIfExists('administration_hosts');
    }
};
