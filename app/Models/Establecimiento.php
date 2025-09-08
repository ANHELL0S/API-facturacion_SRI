<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Establecimiento extends Model
{
    use HasFactory;

    protected $table = "establecimientos";

    protected $fillable = [
        "nombre",
        "numero",
        "direccion",
        "user_id",
    ];

    public function puntosEmisiones()
    {
        return $this->hasMany(PuntoEmision::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function findByIdAndUserId($establecimientoId, $userId)
    {
        return self::where('id', $establecimientoId)->where('user_id', $userId)->first();
    }

    public static function getEstablecimientosByUserId($userId)
    {
        return self::where('user_id', $userId)->get();
    }

    public function countPuntosEmision()
    {
        return $this->puntosEmisiones()->count();
    }

    public static function getEstablecimientosUpdate($userId)
    {
        return self::where('user_id', $userId)->get();
    }

    public static function getEstablecimientosDelete($userId)
    {
        return self::where('user_id', $userId)->get();
    }
}
