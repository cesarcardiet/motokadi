<?php

namespace App\Support\Access;

final class PermissionCatalog
{
    /**
     * @return array<string, array{name: string, group: string}>
     */
    public static function all(): array
    {
        return [
            'panel.admin' => ['name' => 'Entrar al panel de administración', 'group' => 'Administración'],
            'sedes.gestionar' => ['name' => 'Crear y editar sedes', 'group' => 'Administración'],
            'usuarios.gestionar' => ['name' => 'Crear y editar usuarios', 'group' => 'Administración'],
            'motos.gestionar' => ['name' => 'Editar catálogo y precios', 'group' => 'Administración'],
            'roles.gestionar' => ['name' => 'Asignar permisos a los roles', 'group' => 'Administración'],
            'solicitudes.crear' => ['name' => 'Crear una solicitud de financiamiento', 'group' => 'Solicitudes'],
            'solicitudes.revisar' => ['name' => 'Ver la bandeja de solicitudes', 'group' => 'Solicitudes'],
            'solicitudes.preevaluar' => ['name' => 'Aprobar la preevaluación de documentos', 'group' => 'Solicitudes'],
            'solicitudes.verificar_papeles' => ['name' => 'Verificar papeles en la sede', 'group' => 'Solicitudes'],
            'solicitudes.verificar_ubicacion' => ['name' => 'Verificar ubicación y preaprobar', 'group' => 'Solicitudes'],
            'solicitudes.aprobar' => ['name' => 'Dar la aprobación final', 'group' => 'Solicitudes'],
            'solicitudes.rechazar' => ['name' => 'Rechazar una solicitud', 'group' => 'Solicitudes'],
        ];
    }

    /**
     * @return list<string>
     */
    public static function forRole(string $slug): array
    {
        return match ($slug) {
            'administrador' => array_keys(self::all()),
            'vendedor' => [
                'solicitudes.revisar',
                'solicitudes.preevaluar',
                'solicitudes.verificar_papeles',
                'solicitudes.verificar_ubicacion',
                'solicitudes.rechazar',
            ],
            'cliente' => [
                'solicitudes.crear',
            ],
            default => [],
        };
    }
}
