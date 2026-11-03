<?php

namespace Database\Seeders;

use App\Enums\ApplicationChannel;
use App\Enums\ApplicationStatus;
use App\Enums\ContractStatus;
use App\Enums\CreditProfile;
use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Enums\InstallmentStatus;
use App\Enums\VoucherStatus;
use App\Models\ApplicationDocument;
use App\Models\ApplicationEvent;
use App\Models\Branch;
use App\Models\CreditApplication;
use App\Models\Motorcycle;
use App\Models\AppNotification;
use App\Models\PaymentVoucher;
use App\Models\Role;
use App\Models\User;
use App\Services\Financing\ContractService;
use App\Services\Financing\InstallmentSimulator;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DemoLifecycleSeeder extends Seeder
{
    public function run(): void
    {
        $contracts = app(ContractService::class);
        $simulator = app(InstallmentSimulator::class);

        $clientRole = Role::query()->where('slug', 'cliente')->firstOrFail();
        $admin = User::query()->where('email', 'admin@kadimotors.test')->firstOrFail();
        $seller = User::query()->where('email', 'vendedor.naguanagua@kadimotors.test')->firstOrFail();
        $branch = Branch::query()->where('city', 'Naguanagua')->firstOrFail();
        $classic = Motorcycle::query()->where('slug', 'classic-150')->firstOrFail();
        $furioso = Motorcycle::query()->where('slug', 'furioso-250')->firstOrFail();
        $diamante = Motorcycle::query()->where('slug', 'diamante-150')->firstOrFail();

        $signedBase = Carbon::parse('2026-06-01 10:00:00');

        // Full applicant lifecycle: request → approve → contract → cuotas (one in mora).
        $lifecycleClient = User::query()->updateOrCreate(
            ['email' => 'ciclo.completo@kadimotors.test'],
            [
                'name' => 'Luis Ciclo Completo',
                'password' => CatalogSeeder::DEMO_PASSWORD,
                'role_id' => $clientRole->id,
                'branch_id' => null,
                'phone' => '0412-5550101',
                'national_id' => 'V-20100101',
                'credit_profile' => CreditProfile::Intermediate,
                'is_reference_profile' => false,
                'is_active' => true,
            ],
        );

        $lifecycleApp = $this->seedApprovedApplication(
            $lifecycleClient,
            $classic,
            $branch,
            $admin,
            $seller,
            $simulator,
            $signedBase->copy()->subMonths(4),
            'Av. Universidad, Urb. La Granja',
            'Naguanagua',
            '+58 412 5550101',
        );

        $lifecycleContract = $contracts->activateFromApproval(
            $lifecycleApp,
            $signedBase->copy()->subMonths(3),
            $this->demoUnit(1),
        );
        $contracts->applySchedule($lifecycleContract, [
            1 => ['status' => InstallmentStatus::Paid, 'paid_at' => $signedBase->copy()->subMonths(2)->addDays(2)],
            2 => ['status' => InstallmentStatus::Paid, 'paid_at' => $signedBase->copy()->subMonths(1)->addDays(1)],
            3 => ['status' => InstallmentStatus::Paid, 'paid_at' => $signedBase->copy()->addDays(3)],
            4 => [
                'status' => InstallmentStatus::Late,
                'paid_at' => null,
                'days_late' => 18,
                'late_fee' => '7.50',
            ],
        ]);

        $lateInstallment = $lifecycleContract->installments()->where('number', 4)->first();
        if ($lateInstallment && ! $lateInstallment->vouchers()->exists()) {
            $path = 'comprobantes/'.$lifecycleContract->id.'/demo-cuota-4.jpg';
            Storage::disk('public')->put($path, base64_decode(
                '/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGBxAQEBUQEBAVFRUVFRUVFRUVFRUWFxUVFRUYHSggGBolGxUVITEhJSkrLi4uFx8zODMtNygtLisBCgoKDg0OGxAQGy0lHyUtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLf/AABEIAAEAAQMBIgACEQEDEQH/xAAbAAABBQEBAAAAAAAAAAAAAAADAAECBAUGB//EAD0QAAIBAwMCBAMFBgcBAAAAAAECAwAEEQUSITFBBhNRYXEUIoGRMkKhsQcjUmLB0fAWJDNygvH/xAAZAQADAQEBAAAAAAAAAAAAAAAAAQIDBAX/xAAhEQACAgIDAQADAQAAAAAAAAAAAQIRAxIhMQRBEyJRYf/aAAwDAQACEQMRAD8A9/ooooA//9k='
            ));

            $rate = app(\App\Services\Settings\ExchangeRate::class)->current();
            $voucher = PaymentVoucher::query()->create([
                'installment_id' => $lateInstallment->id,
                'user_id' => $lifecycleClient->id,
                'path' => $path,
                'original_name' => 'comprobante-cuota-4.jpg',
                'amount' => $lateInstallment->amount,
                'exchange_rate' => $rate,
                'amount_ves' => app(\App\Services\Settings\ExchangeRate::class)->toVes($lateInstallment->amount, $rate),
                'rate_frozen_at' => now(),
                'paid_on' => now()->toDateString(),
                'reference' => 'REF-DEMO-404',
                'status' => VoucherStatus::Pending,
            ]);

            AppNotification::query()->create([
                'user_id' => $lifecycleClient->id,
                'type' => 'payment.submitted',
                'title' => 'Comprobante enviado',
                'body' => 'Tu comprobante de la cuota #4 quedó en revisión.',
                'link' => route('client.payments.index'),
            ]);

            User::query()
                ->whereHas('role.permissions', fn ($query) => $query->where('slug', 'panel.admin'))
                ->each(function (User $adminUser) use ($lifecycleClient, $voucher) {
                    AppNotification::query()->create([
                        'user_id' => $adminUser->id,
                        'type' => 'payment.submitted',
                        'title' => 'Nuevo comprobante',
                        'body' => $lifecycleClient->name.' subió pago de la cuota #4.',
                        'link' => route('admin.payments.show', $voucher),
                    ]);
                });
        }

        // Three credit-profile reference clients.
        $profiles = [
            [
                'email' => 'perfil.buenpagador@kadimotors.test',
                'name' => 'Ana Buen Pagador',
                'national_id' => 'V-20100201',
                'phone' => '0412-5550201',
                'whatsapp' => '+58 412 5550201',
                'profile' => CreditProfile::Reliable,
                'moto' => $diamante,
                'signed' => $signedBase->copy()->subMonths(5),
                'schedule' => [
                    1 => ['status' => InstallmentStatus::Paid, 'paid_at' => $signedBase->copy()->subMonths(4)],
                    2 => ['status' => InstallmentStatus::Paid, 'paid_at' => $signedBase->copy()->subMonths(3)],
                    3 => ['status' => InstallmentStatus::Paid, 'paid_at' => $signedBase->copy()->subMonths(2)],
                    4 => ['status' => InstallmentStatus::Paid, 'paid_at' => $signedBase->copy()->subMonths(1)],
                    5 => ['status' => InstallmentStatus::Paid, 'paid_at' => $signedBase->copy()],
                ],
            ],
            [
                'email' => 'perfil.intermedio@kadimotors.test',
                'name' => 'Carlos Intermedio',
                'national_id' => 'V-20100202',
                'phone' => '0412-5550202',
                'whatsapp' => '+58 412 5550202',
                'profile' => CreditProfile::Intermediate,
                'moto' => $classic,
                'signed' => $signedBase->copy()->subMonths(4),
                'schedule' => [
                    1 => ['status' => InstallmentStatus::Paid, 'paid_at' => $signedBase->copy()->subMonths(3)->addDays(4)],
                    2 => [
                        'status' => InstallmentStatus::Paid,
                        'paid_at' => $signedBase->copy()->subMonths(2)->addDays(12),
                        'days_late' => 12,
                        'late_fee' => '5.00',
                    ],
                    3 => ['status' => InstallmentStatus::Paid, 'paid_at' => $signedBase->copy()->subMonths(1)->addDays(2)],
                    4 => ['status' => InstallmentStatus::Pending],
                ],
            ],
            [
                'email' => 'perfil.malpagador@kadimotors.test',
                'name' => 'Pedro Mal Historial',
                'national_id' => 'V-20100203',
                'phone' => '0412-5550203',
                'whatsapp' => '+58 412 5550203',
                'profile' => CreditProfile::Poor,
                'moto' => $furioso,
                'signed' => $signedBase->copy()->subMonths(6),
                'schedule' => [
                    1 => ['status' => InstallmentStatus::Paid, 'paid_at' => $signedBase->copy()->subMonths(5)->addDays(20), 'days_late' => 20, 'late_fee' => '8.00'],
                    2 => ['status' => InstallmentStatus::Late, 'days_late' => 45, 'late_fee' => '15.00'],
                    3 => ['status' => InstallmentStatus::Late, 'days_late' => 25, 'late_fee' => '12.00'],
                    4 => ['status' => InstallmentStatus::Pending],
                ],
            ],
        ];

        foreach ($profiles as $row) {
            $user = User::query()->updateOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'password' => CatalogSeeder::DEMO_PASSWORD,
                    'role_id' => $clientRole->id,
                    'branch_id' => null,
                    'phone' => $row['phone'],
                    'national_id' => $row['national_id'],
                    'credit_profile' => $row['profile'],
                    'is_reference_profile' => true,
                    'is_active' => true,
                ],
            );

            $application = $this->seedApprovedApplication(
                $user,
                $row['moto'],
                $branch,
                $admin,
                $seller,
                $simulator,
                $row['signed']->copy()->subDays(10),
                'Referencia '.$row['profile']->label(),
                'Naguanagua',
                $row['whatsapp'],
            );

            $contract = $contracts->activateFromApproval(
                $application,
                $row['signed'],
                $this->demoUnit($user->id),
            );
            $contracts->applySchedule($contract, $row['schedule']);
        }
    }

    /**
     * @return array{unit_cartel: string, unit_plate: string, unit_serial: string}
     */
    private function demoUnit(int $seed): array
    {
        $n = str_pad((string) ($seed % 10000), 4, '0', STR_PAD_LEFT);

        return [
            'unit_cartel' => 'CT-'.$n,
            'unit_plate' => 'KA'.$n.'VE',
            'unit_serial' => 'KADI'.$n.'CHS'.str_pad((string) (($seed * 7) % 100000), 5, '0', STR_PAD_LEFT),
        ];
    }

    private function seedApprovedApplication(
        User $client,
        Motorcycle $motorcycle,
        Branch $branch,
        User $admin,
        User $seller,
        InstallmentSimulator $simulator,
        Carbon $submittedAt,
        string $address,
        string $city,
        string $whatsapp,
    ): CreditApplication {
        $existing = CreditApplication::query()
            ->where('user_id', $client->id)
            ->where('motorcycle_id', $motorcycle->id)
            ->where('status', ApplicationStatus::Approved->value)
            ->first();

        if ($existing) {
            return $existing;
        }

        // Close any leftover open apps so demos stay unique per reference client.
        CreditApplication::query()
            ->where('user_id', $client->id)
            ->active()
            ->update([
                'status' => ApplicationStatus::Rejected->value,
                'rejection_reason' => 'Reemplazada por simulación de referencia.',
            ]);

        $quote = $simulator->quote($motorcycle->price, $motorcycle->initial_payment, $motorcycle->term_months);

        $application = CreditApplication::query()->create([
            'user_id' => $client->id,
            'motorcycle_id' => $motorcycle->id,
            'branch_id' => $branch->id,
            'channel' => ApplicationChannel::Online,
            'status' => ApplicationStatus::Approved,
            'color' => $motorcycle->colors[0] ?? 'Negro',
            'price' => $quote['price'],
            'initial_payment' => $quote['initial'],
            'balance' => $quote['balance'],
            'monthly_payment' => $quote['monthly'],
            'term_months' => $quote['months'],
            'address' => $address,
            'city' => $city,
            'whatsapp_phone' => $whatsapp,
            'latitude' => 10.2501000,
            'longitude' => -68.0101000,
            'location_verified_at' => $submittedAt->copy()->addDays(3),
            'submitted_at' => $submittedAt,
        ]);

        foreach (DocumentType::required() as $type) {
            ApplicationDocument::query()->create([
                'credit_application_id' => $application->id,
                'type' => $type,
                'path' => '',
                'original_name' => 'Simulado '.$type->label(),
                'status' => DocumentStatus::Accepted,
                'reviewed_by' => $seller->id,
                'reviewed_at' => $submittedAt->copy()->addDay(),
            ]);
        }

        ApplicationEvent::query()->create([
            'credit_application_id' => $application->id,
            'user_id' => $admin->id,
            'action' => ApplicationStatus::Approved->value,
            'from_status' => ApplicationStatus::Preapproved,
            'to_status' => ApplicationStatus::Approved,
            'note' => 'Simulación: solicitud aprobada y lista para contrato.',
            'created_at' => $submittedAt->copy()->addDays(5),
            'updated_at' => $submittedAt->copy()->addDays(5),
        ]);

        return $application;
    }
}
