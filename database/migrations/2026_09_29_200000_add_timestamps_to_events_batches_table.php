<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Lotes passam a ter created_at/updated_at para o superapp receber só o que mudou (?desde=). Os
 * lotes que já existem ganham a data de agora, senão nunca apareceriam num delta.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events_batches', function (Blueprint $table) {
            $table->timestamps();
        });

        DB::table('events_batches')->update(['created_at' => now(), 'updated_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('events_batches', function (Blueprint $table) {
            $table->dropTimestamps();
        });
    }
};
