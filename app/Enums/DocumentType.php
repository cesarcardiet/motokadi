<?php

namespace App\Enums;

enum DocumentType: string
{
    case Cedula = 'cedula';
    case Rif = 'rif';
    case License = 'licencia_2da';
    case Employment = 'constancia_trabajo';

    public function label(): string
    {
        return match ($this) {
            self::Cedula => 'Copia de cédula actualizada',
            self::Rif => 'RIF actualizado',
            self::License => 'Licencia y certificado de 2DA',
            self::Employment => 'Constancia de trabajo',
        };
    }

    /**
     * @return list<self>
     */
    public static function required(): array
    {
        return self::cases();
    }
}
