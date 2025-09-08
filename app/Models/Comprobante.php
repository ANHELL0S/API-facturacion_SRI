<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comprobante extends Model
{
    use HasFactory;

    protected $table = "comprobantes";
    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'estado' => 'string',
        'tipo_comprobante' => 'integer',
        'ambiente' => 'integer',
        'fecha_emision' => 'datetime',
        'procesado_en' => 'datetime',
        'fecha_autorizacion' => 'datetime',
        'payload' => 'array',
    ];

    protected $fillable = [
        "user_id",
        "tipo_comprobante",
        "establecimiento",
        "punto_emision",
        "secuencial",
        "clave_acceso",
        "ambiente",
        "cliente_email",
        "cliente_ruc",
        "estado",
        "error_code",
        "error_message",
        "intentos",
        "fecha_emision",
        "procesado_en",
        "fecha_autorizacion",
        "payload"
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'user_id',
        'intentos',
    ];

    /**
     * Summary of booted
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($comprobante) {
            $comprobante->id = \Str::uuid();
        });
    }

    public function puntoEmision()
    {
        return $this->belongsTo(PuntoEmision::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function findByClaveAcceso($claveAcceso)
    {
        return self::where('clave_acceso', $claveAcceso)->firstOrFail();
    }

    /**
     * Get the fecha_autorizacion attribute.
     *
     * @param  string|null  $value
     * @return string|null
     */
    public function getFechaAutorizacionAttribute($value)
    {
        if (is_null($value)) {
            return null;
        }
        return \Carbon\Carbon::parse($value)->format('Y-m-d H:i:s');
    }
}
