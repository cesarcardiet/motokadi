<?php

namespace App\Enums;

enum DocumentStatus: string
{
    case Pending = 'pendiente';
    case Accepted = 'aceptado';
    case Rejected = 'rechazado';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::Accepted => 'Aceptado',
            self::Rejected => 'Rechazado',
        };
    }
}
