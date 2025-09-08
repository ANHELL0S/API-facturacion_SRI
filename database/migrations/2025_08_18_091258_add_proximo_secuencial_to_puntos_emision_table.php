<?php

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
        Schema::table('puntos_emision', function (Blueprint $table) {
            $table->string('proximo_secuencial', 9)->default('000000001')->after('ultimoSecuencial');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('puntos_emision', function (Blueprint $table) {
            $table->dropColumn('proximo_secuencial');
        });
    }
};
