<?php

namespace App\Enums;

enum ContractStatus: string
{
    case Active = 'activo';
    case Closed = 'cerrado';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Activo',
            self::Closed => 'Cerrado',
        };
    }
}
