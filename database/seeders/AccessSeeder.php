<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Support\Access\PermissionCatalog;
use Illuminate\Database\Seeder;

class AccessSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PermissionCatalog::all() as $slug => $meta) {
            Permission::query()->updateOrCreate(
                ['slug' => $slug],
                ['name' => $meta['name'], 'group' => $meta['group']],
            );
        }

        $roles = [
            'administrador' => ['Administrador', 'Administración completa, incluida la aprobación final.'],
            'vendedor' => ['Vendedor de sede', 'Revisa documentos, papeles y ubicación de su sede. No aprueba el crédito final.'],
            'cliente' => ['Cliente', 'Carga recaudos y consulta el estado de su solicitud.'],
        ];

        foreach ($roles as $slug => [$name, $description]) {
            $role = Role::query()->updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'description' => $description],
            );

            $permissionIds = Permission::query()
                ->whereIn('slug', PermissionCatalog::forRole($slug))
                ->pluck('id');

            $role->permissions()->sync($permissionIds);
        }
    }
}
