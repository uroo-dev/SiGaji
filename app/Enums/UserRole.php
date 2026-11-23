<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case SUPERVISOR = 'supervisor';
    case EMPLOYEE = 'employee';

    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Admin',
            self::SUPERVISOR => 'Supervisor',
            self::EMPLOYEE => 'Karyawan',
        };
    }
}
