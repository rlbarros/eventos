<?php

use App\Models\Church;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('persons', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Church::class)->nullable()->constrained();
            $table->string('name');
            $table->date('birth_date')->nullable();
            $table->string('phone', 20)->nullable();
            $table->binary('avatar')->nullable();

            // self-referencing relations
            $table->foreignId('father_id')->nullable()->constrained('persons');
            $table->foreignId('mother_id')->nullable()->constrained('persons');
            $table->foreignId('spouse_id')->nullable()->constrained('persons');

            $table->enum('function', ['Membro', 'Pastor', 'Convidado', 'Obreiro', 'Diácono', 'Pregador de Conferência', 'Presbítero', 'Evangelista', 'Bispo'])->nullable(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->dropForeign(['church_id']);
            $table->dropForeign(['father_id']);
            $table->dropForeign(['mother_id']);
            $table->dropForeign(['spouse_id']);
        });
        Schema::dropIfExists('persons');
    }
};
