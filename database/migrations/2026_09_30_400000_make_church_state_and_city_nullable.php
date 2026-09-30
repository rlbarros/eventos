<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Igrejas criadas a partir do catálogo da administração (igrejas:vincular-administracao --criar)
     * ainda não têm cidade, e às vezes nem estado.
     */
    public function up(): void
    {
        Schema::table('churches', function (Blueprint $table) {
            $table->unsignedBigInteger('state_id')->nullable()->change();
            $table->unsignedBigInteger('city_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('churches', function (Blueprint $table) {
            $table->unsignedBigInteger('state_id')->nullable(false)->change();
            $table->unsignedBigInteger('city_id')->nullable(false)->change();
        });
    }
};
