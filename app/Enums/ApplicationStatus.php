<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case Preevaluation = 'preevaluacion';
    case Papers = 'verificacion_papeles';
    case Location = 'verificacion_ubicacion';
    case Preapproved = 'preaprobada';
    case Approved = 'aprobada';
    case Rejected = 'rechazada';

    public function label(): string
    {
        return match ($this) {
            self::Preevaluation => 'Preevaluación',
            self::Papers => 'Verificación de papeles',
            self::Location => 'Visita / prueba de vista',
            self::Preapproved => 'Preaprobada',
            self::Approved => 'Aprobación final',
            self::Rejected => 'Rechazada',
        };
    }

    public function isOpen(): bool
    {
        return ! in_array($this, [self::Approved, self::Rejected], true);
    }
}
