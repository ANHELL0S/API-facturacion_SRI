<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErrorSri extends Model
{
    use HasFactory;

    protected $table = "errores_sri";

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'description',
        'reason',
        'validation_type',
        'is_warning'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_warning' => 'boolean',
    ];

    /**
     * Summary of findByCode
     * @param string $code
     * @return ErrorSri|null
     */
    public static function findByCode(string $code)
    {
        return static::where('code', $code)->first();
    }
}
