<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Uma pessoa daqui por pessoa da administração. Se há duplicatas, rode antes
     * `php artisan pessoas:unir-duplicadas --aplicar`.
     */
    public function up(): void
    {
        $duplicates = DB::table('persons')->whereNotNull('administration_person_id')
            ->groupBy('administration_person_id')->havingRaw('COUNT(*) > 1')->count();
        if ($duplicates > 0) {
            throw new RuntimeException("Há {$duplicates} pessoas da administração duplicadas: rode `php artisan pessoas:unir-duplicadas --aplicar` e depois migre de novo.");
        }

        Schema::table('persons', function (Blueprint $table) {
            $table->dropIndex(['administration_person_id']);
            $table->unique('administration_person_id');
        });
    }

    public function down(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->dropUnique(['administration_person_id']);
            $table->index('administration_person_id');
        });
    }
};
