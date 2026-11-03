<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Motorcycle;
use App\Models\Role;
use App\Models\User;
use App\Services\Financing\InstallmentSimulator;
use App\Support\Catalog\KadiCatalog;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public const DEMO_PASSWORD = 'Kadi2026!';

    public function run(): void
    {
        $naguanagua = Branch::query()->updateOrCreate(
            ['name' => 'Kadi Motors Naguanagua'],
            [
                'city' => 'Naguanagua',
                'address' => 'Av. Universidad con Av. Paseo Valencia, galpón 1',
                'phone' => '0241-0000000',
                'is_active' => true,
            ],
        );

        Branch::query()->updateOrCreate(
            ['name' => 'Kadi Motors Barquisimeto'],
            [
                'city' => 'Barquisimeto',
                'address' => 'Carrera 19 esquina calle 46',
                'phone' => '0251-0000000',
                'is_active' => true,
            ],
        );

        foreach (KadiCatalog::motorcycles() as $row) {
            Motorcycle::query()->updateOrCreate(
                ['slug' => $row['slug']],
                [
                    ...$row,
                    'term_months' => InstallmentSimulator::MONTHS,
                    'is_active' => true,
                ],
            );
        }

        $adminRole = Role::query()->where('slug', 'administrador')->firstOrFail();
        $sellerRole = Role::query()->where('slug', 'vendedor')->firstOrFail();
        $clientRole = Role::query()->where('slug', 'cliente')->firstOrFail();

        User::query()->updateOrCreate(
            ['email' => 'admin@kadimotors.test'],
            [
                'name' => 'Administración Kadi',
                'password' => self::DEMO_PASSWORD,
                'role_id' => $adminRole->id,
                'branch_id' => null,
                'phone' => '0414-0000001',
                'national_id' => 'V-10000001',
                'is_active' => true,
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'vendedor.naguanagua@kadimotors.test'],
            [
                'name' => 'Vendedor Naguanagua',
                'password' => self::DEMO_PASSWORD,
                'role_id' => $sellerRole->id,
                'branch_id' => $naguanagua->id,
                'phone' => '0414-0000002',
                'national_id' => 'V-10000002',
                'is_active' => true,
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'cliente@kadimotors.test'],
            [
                'name' => 'Cliente de prueba',
                'password' => self::DEMO_PASSWORD,
                'role_id' => $clientRole->id,
                'branch_id' => null,
                'phone' => '0414-0000003',
                'national_id' => 'V-10000003',
                'is_active' => true,
            ],
        );
    }
}
