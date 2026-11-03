<?php

namespace App\Enums;

enum CreditProfile: string
{
    case Reliable = 'buen_pagador';
    case Intermediate = 'intermedio';
    case Poor = 'mal_pagador';

    public function label(): string
    {
        return match ($this) {
            self::Reliable => 'Buen pagador',
            self::Intermediate => 'Pagador intermedio',
            self::Poor => 'Mal historial / mora',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Reliable => 'Paga todas las cuotas a tiempo. Referencia positiva.',
            self::Intermediate => 'Paga la mayoría, con algún atraso ocasional recuperado.',
            self::Poor => 'Tiene cuotas en mora y alto riesgo de cobro.',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Reliable => 'ok',
            self::Intermediate => 'warn',
            self::Poor => 'bad',
        };
    }
}
