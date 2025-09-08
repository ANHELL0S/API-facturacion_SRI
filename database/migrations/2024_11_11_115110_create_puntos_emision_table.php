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
        Schema::create('puntos_emision', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('establecimiento_id');
            $table->string('nombre', 100);
            $table->string('numero', 3);
            $table->string('ultimoSecuencial', 9)->default('000000000');
            $table->string('max_secuenciales')->default('999999999');
            $table->boolean('estado')->default(true);
            $table->timestamp('reset_at')->nullable();
            $table->timestamps();
            $table->foreign('establecimiento_id')->references('id')->on('establecimientos')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('puntos_emisiones');
    }

};
