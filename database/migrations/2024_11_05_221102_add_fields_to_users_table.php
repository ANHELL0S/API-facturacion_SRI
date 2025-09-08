<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\TarifasEnum;
use App\Enums\AmbientesEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('active_account')->default(true);
            $table->text('signature_path')->nullable();
            $table->string('signature_key')->nullable();
            $table->string('tarifa')->default(TarifasEnum::COMPROBANTE->value)->nullable(); // Tarifa opcional para administradores
            $table->char('ambiente', 1)->default(AmbientesEnum::PRUEBAS->value);
            $table->char('ruc', 13)->nullable()->unique();
            $table->string('razonSocial')->nullable();
            $table->string('nombreComercial')->nullable();
            $table->string('dirMatriz')->nullable();
            $table->string('contribuyenteEspecial')->nullable();
            $table->boolean('obligadoContabilidad')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'active_account', 'signature_path', 'signature_key', 'tarifa',
                'ambiente', 'ruc', 'razonSocial', 'nombreComercial',
                'dirMatriz', 'contribuyenteEspecial', 'obligadoContabilidad'
            ]);
        });
    }
};
