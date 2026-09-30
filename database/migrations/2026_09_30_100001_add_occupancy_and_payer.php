<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // taxa por faixa de ocupação do quarto (pessoas na reserva); nulo = vale para qualquer ocupação
        Schema::table('events_fees', function (Blueprint $table) {
            $table->unsignedTinyInteger('min_occupants')->nullable()->after('category');
            $table->unsignedTinyInteger('max_occupants')->nullable()->after('min_occupants');
        });

        // pagador da reserva: nulo = a própria pessoa paga; preenchido = outra pessoa do evento paga por ela
        Schema::table('events_participants_allocations', function (Blueprint $table) {
            $table->foreignId('payer_person_id')->nullable()->after('person_id')
                ->constrained('persons')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('events_participants_allocations', function (Blueprint $table) {
            $table->dropForeign(['payer_person_id']);
            $table->dropColumn('payer_person_id');
        });

        Schema::table('events_fees', function (Blueprint $table) {
            $table->dropColumn(['min_occupants', 'max_occupants']);
        });
    }
};
