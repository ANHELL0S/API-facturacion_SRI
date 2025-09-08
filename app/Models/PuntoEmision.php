<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PuntoEmision extends Model
{
    use HasFactory;

    protected $table = "puntos_emision";

    protected $casts = [
        'estado' => 'boolean',
    ];

    protected $fillable = [
        "establecimiento_id",
        "nombre",
        "numero",
        "ultimoSecuencial",
        //"max_puntos_emision",
        "max_secuenciales",
        "estado",
        "reset_at"
    ];

    public function establecimiento()
    {
        return $this->belongsTo(Establecimiento::class);
    }

    public function comprobantes()
    {
        return $this->hasMany(Comprobante::class);
    }

    public static function getEstablecimientoIds()
    {
        return self::pluck('establecimiento_id');
    }

    public function reset()
    {
        //$this->numero = '000';
        $this->ultimoSecuencial = '000000000';
        $this->reset_at = now();
        $this->save();
    }

    public function deactivate()
    {
        $this->activo = false;
        $this->save();
    }

    public function activate()
    {
        $this->activo = true;
        $this->save();
    }
}
