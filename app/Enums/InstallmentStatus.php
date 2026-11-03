<?php

namespace App\Enums;

enum InstallmentStatus: string
{
    case Pending = 'pendiente';
    case Paid = 'pagada';
    case Late = 'mora';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::Paid => 'Pagada',
            self::Late => 'En mora',
        };
    }
}
