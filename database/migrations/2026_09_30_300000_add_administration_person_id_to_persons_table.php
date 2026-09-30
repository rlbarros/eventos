<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            // pessoas.id na administração, gravado pelo sync de pessoas (o vínculo antes era só o CPF)
            $table->unsignedBigInteger('administration_person_id')->nullable()->after('cpf')->index();
        });
    }

    public function down(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->dropColumn('administration_person_id');
        });
    }
};
