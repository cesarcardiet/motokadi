<?php

namespace App\Support\Catalog;

final class KadiCatalog
{
    /**
     * Classic, Diamante y Tundra usan los montos de la tabla de ejemplo.
     * El resto conserva la misma fórmula con un precio estimado de referencia.
     *
     * @return list<array{
     *     name: string,
     *     slug: string,
     *     line: string,
     *     displacement: int,
     *     summary: string,
     *     price: string,
     *     initial_payment: string,
     *     price_source: string,
     *     colors: list<string>,
     *     sort_order: int
     * }>
     */
    public static function motorcycles(): array
    {
        return [
            self::row('Kadi Furioso 250', 'furioso-250', 'enduro', 250, 'Enduro de 250 cc, la referencia de trocha del catálogo.', '2150.00', '650.00', 'estimado', ['Negro', 'Rojo'], 10),
            self::row('Kadi Power 200', 'power-200', 'enduro', 200, 'Enduro de 200 cc para salir de la ciudad sin dejar el diario.', '1800.00', '540.00', 'estimado', ['Negro', 'Blanco', 'Rojo'], 20),
            self::row('Kadi Playera 200', 'playera-200', 'atv', 200, 'Cuatrimoto de 200 cc para arena, monte y uso familiar.', '2300.00', '690.00', 'estimado', ['Rojo', 'Negro', 'Verde'], 30),
            self::row('Kadi Classic 150', 'classic-150', 'sincronica', 150, 'Sincrónica de 150 cc. Montos tomados de la tabla de ejemplo.', '950.00', '280.00', 'ejemplo', ['Negro', 'Rojo', 'Azul'], 40),
            self::row('Kadi Lovis 150', 'lovis-150', 'sincronica', 150, 'Sincrónica de trabajo, 150 cc, pensada para el motorizado diario.', '1280.00', '380.00', 'estimado', ['Negro', 'Rojo', 'Gris'], 50),
            self::row('Kadi Kava 150', 'kava-150', 'sincronica', 150, 'Estilo custom de 150 cc con presencia de calle.', '1290.00', '390.00', 'estimado', ['Negro', 'Cromado'], 60),
            self::row('Kadi Expo 150', 'expo-150', 'sincronica', 150, 'Sincrónica económica de 150 cc para empezar.', '1190.00', '360.00', 'estimado', ['Negro', 'Azul', 'Rojo'], 70),
            self::row('Kadi KD 150', 'kd-150', 'sincronica', 150, 'Deportiva de 150 cc con llanta ancha y look de calle.', '1340.00', '400.00', 'estimado', ['Negro', 'Rojo'], 80),
            self::row('Kadi Pionero 200', 'pionero-200', 'sincronica', 200, 'Sincrónica de 200 cc para distancias más largas.', '1380.00', '420.00', 'estimado', ['Negro', 'Gris', 'Rojo'], 90),
            self::row('Kadi Terzo 250', 'terzo-250', 'automatica', 250, 'Maxi-scooter de 250 cc, cómodo para ciudad y carretera.', '1850.00', '560.00', 'estimado', ['Negro', 'Blanco', 'Gris'], 100),
            self::row('Kadi Cristal 150', 'cristal-150', 'automatica', 150, 'Scooter urbano de 150 cc, automático y fácil de manejar.', '1300.00', '390.00', 'estimado', ['Blanco', 'Negro', 'Rojo'], 110),
            self::row('Kadi Diamante 150', 'diamante-150', 'automatica', 150, 'Scooter de 150 cc. Montos tomados de la tabla de ejemplo.', '1400.00', '500.00', 'ejemplo', ['Negro', 'Rojo', 'Azul'], 120),
            self::row('Kadi Tundra 150', 'tundra-150', 'automatica', 150, 'Mini moto urbana de 150 cc. Montos tomados de la tabla de ejemplo.', '1420.00', '450.00', 'ejemplo', ['Negro', 'Blanco', 'Rojo'], 130),
            self::row('Kadi Carga 250', 'carga-250', 'carguero', 250, 'Carguero de 250 cc con cabina techada.', '4130.00', '1240.00', 'estimado', ['Blanco', 'Rojo'], 140),
            self::row('Kadi Carga-D 300', 'carga-d-300', 'carguero', 300, 'Carguero de 300 cc con cama de volteo hidráulico.', '4900.00', '1470.00', 'estimado', ['Blanco', 'Naranja'], 150),
            self::row('Kadi Carga-S 250', 'carga-s-250', 'carguero', 250, 'Carguero básico de 250 cc con cama abierta.', '3600.00', '1080.00', 'estimado', ['Blanco', 'Azul'], 160),
        ];
    }

    public static function lineLabel(string $line): string
    {
        return match ($line) {
            'enduro' => 'Enduro',
            'atv' => 'Cuatrimoto',
            'sincronica' => 'Sincrónica',
            'automatica' => 'Automática',
            'carguero' => 'Carguero',
            default => $line,
        };
    }

    /**
     * @param  list<string>  $colors
     * @return array{
     *     name: string,
     *     slug: string,
     *     line: string,
     *     displacement: int,
     *     summary: string,
     *     price: string,
     *     initial_payment: string,
     *     price_source: string,
     *     colors: list<string>,
     *     sort_order: int
     * }
     */
    private static function row(
        string $name,
        string $slug,
        string $line,
        int $displacement,
        string $summary,
        string $price,
        string $initial,
        string $source,
        array $colors,
        int $sort,
    ): array {
        return [
            'name' => $name,
            'slug' => $slug,
            'line' => $line,
            'displacement' => $displacement,
            'summary' => $summary,
            'price' => $price,
            'initial_payment' => $initial,
            'price_source' => $source,
            'colors' => $colors,
            'sort_order' => $sort,
        ];
    }
}
