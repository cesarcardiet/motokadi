<?php

namespace App\Enums;

enum ApplicationChannel: string
{
    case Online = 'en_linea';
    case Branch = 'en_sede';

    public function label(): string
    {
        return match ($this) {
            self::Online => 'Subir documentos y esperar la llamada',
            self::Branch => 'Llevar la carpeta a la sede',
        };
    }
}
