<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;
use App\Enums\TarifasEnum;
use App\Enums\AmbientesEnum;

/**
 * Class User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $signature_path
 * @property string $signature_key
 * @property \DateTime $signature_expires_at
 * @property bool $active_account
 * @property string $tarifa
 * @property int $ambiente
 * @property string $ruc
 * @property string $razonSocial
 * @property string $nombreComercial
 * @property string $dirMatriz
 * @property string $contribuyenteEspecial
 * @property string $obligadoContabilidad
 * @property \DateTime $created_at
 * @property \DateTime $updated_at
 */
class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasRoles;

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'signature_path',
        'signature_key',
        'signature_expires_at',
        'active_account',
        'tarifa',
        'ambiente',
        'ruc',
        'razonSocial',
        'nombreComercial',
        'dirMatriz',
        'contribuyenteEspecial',
        'obligadoContabilidad',
        'enviar_factura_por_correo',
        'from_email',
        'logo_path',
        'tipo_impuesto',
        'codigo_porcentaje_iva',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'signature_path',
        'signature_key',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'clave_firma' => 'hashed',
            'ambiente' => 'integer',
            'obligadoContabilidad' => 'boolean',
            'enviar_factura_por_correo' => 'boolean',
        ];
    }

    /**
     * Summary of boot
     * @throws \InvalidArgumentException
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($user) {
            try {
                // Validación para 'tipo_tarifa'
                if ($user->tarifa) {
                    if (!in_array($user->tarifa, TarifasEnum::values())) {
                        throw new \InvalidArgumentException('El valor de tarifa es inválido');
                    }
                }

                // Validación para 'ambiente'
                if ($user->ambiente) {
                    if (!in_array($user->ambiente, AmbientesEnum::values())) {
                        throw new \InvalidArgumentException('El valor de ambiente es inválido');
                    }
                }
            } catch (\Exception $e) {
                report($e);
                throw $e;
            }
        });
    }

    public function establecimientos()
    {
        return $this->hasMany(Establecimiento::class);
    }

    public function comprobantes()
    {
        return $this->hasMany(Comprobante::class);
    }
}
