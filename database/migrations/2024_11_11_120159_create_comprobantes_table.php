<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\AmbientesEnum;
use App\Enums\TipoComprobanteEnum;
use App\Enums\EstadosComprobanteEnum;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('comprobantes', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');

            $table->string('tipo_comprobante', 2)->default(TipoComprobanteEnum::FACTURA->value);
            $table->string('establecimiento', 3)->nullable();
            $table->string('punto_emision', 3)->nullable();
            $table->string('secuencial', 9)->nullable();
            $table->string('clave_acceso', 49)->unique()->nullable();
            $table->string('ambiente', 1)->default(AmbientesEnum::PRUEBAS->value);

            $table->json('payload')->nullable(); // guarda los datos originales del comprobante
            $table->string('cliente_email', 100)->nullable();
            $table->string('cliente_ruc', 13)->nullable();

            $table->string('estado', 20)->default(EstadosComprobanteEnum::PENDIENTE->value);
            $table->text('error_code')->nullable();
            $table->text('error_message')->nullable();
            $table->tinyInteger('intentos')->default(0); // para monitoreo

            $table->dateTime('fecha_emision');
            $table->timestamp('procesado_en')->nullable(); // para saber cuando termino
            $table->dateTime('fecha_autorizacion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comprobantes');
    }
};
