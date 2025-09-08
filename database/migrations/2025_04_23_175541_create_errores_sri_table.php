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
        Schema::create('errores_sri', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->text('description');
            $table->text('reason');
            $table->enum('validation_type', [
                'RECEPCIÓN',
                'AUTORIZACIÓN',
                'EMISOR',
                'EMISOR/RECEPCIÓN'
            ])->nullable();
            $table->boolean('is_warning')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('errores_sri');
    }
};
