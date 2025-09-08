<?php

namespace App\Enums;

enum PermissionsEnum: string
{
    case VIEW_USERS = 'view users';
    case CREATE_USERS = 'create users';
    case EDIT_USERS = 'edit users';
    case DELETE_USERS = 'delete users';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}