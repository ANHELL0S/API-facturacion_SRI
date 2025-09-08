<?php

namespace App\Enums;

enum RolesEnum: string
{
    case ADMIN = 'admin';
    case CLIENTE = 'client';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}