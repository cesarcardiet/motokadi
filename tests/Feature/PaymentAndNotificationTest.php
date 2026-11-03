<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\InstallmentStatus;
use App\Enums\VoucherStatus;
use App\Models\AppNotification;
use App\Models\Motorcycle;
use App\Models\PaymentVoucher;
use App\Models\User;
use App\Services\Applications\ApplicationWorkflow;
use App\Services\Financing\ContractService;
use App\Services\Financing\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentAndNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_voucher_stays_pending_until_admin_approves_and_notifications_fire(): void
    {
        $this->seed();
        Storage::fake('public');

        $client = User::query()->where('email', 'cliente@kadimotors.test')->firstOrFail();
        $admin = User::query()->where('email', 'admin@kadimotors.test')->firstOrFail();
        $seller = User::query()->where('email', 'vendedor.naguanagua@kadimotors.test')->firstOrFail();
        $motorcycle = Motorcycle::query()->where('slug', 'classic-150')->firstOrFail();
        $branch = $seller->branch;

        $workflow = app(ApplicationWorkflow::class);
        $contracts = app(ContractService::class);
        $payments = app(PaymentService::class);

        $uploads = [];
        foreach (\App\Enums\DocumentType::cases() as $type) {
            $uploads[$type->value] = UploadedFile::fake()->create($type->value.'.pdf', 20, 'application/pdf');
        }

        $application = $workflow->open($client, [
            'motorcycle_id' => $motorcycle->id,
            'branch_id' => $branch->id,
            'channel' => 'en_linea',
            'color' => 'Negro',
            'address' => 'Av. Universidad',
            'city' => 'Naguanagua',
            'whatsapp_phone' => '+58 412 0000000',
            'latitude' => 10.25,
            'longitude' => -68.01,
        ], $uploads);

        $this->assertTrue(
            AppNotification::query()->where('user_id', $client->id)->where('type', 'application.created')->exists()
        );
        $this->assertTrue(
            AppNotification::query()->where('user_id', $admin->id)->where('type', 'application.created')->exists()
        );

        $workflow->completePreevaluation($seller, $application->fresh());
        foreach ($application->fresh()->documents as $document) {
            $workflow->reviewDocument($seller, $document, \App\Enums\DocumentStatus::Accepted, null);
        }
        $workflow->verifyPapers($seller, $application->fresh());
        $workflow->verifyLocation($seller, $application->fresh(), 10.25, -68.01, 'Visita confirmada en domicilio.');
        $workflow->approve($admin, $application->fresh(), [
            'unit_cartel' => 'CT-9901',
            'unit_plate' => 'XY99ZZ',
            'unit_serial' => 'KADI9901CHS777',
        ]);

        $this->assertSame(ApplicationStatus::Approved, $application->fresh()->status);
        $this->assertTrue($application->fresh()->contract->hasAssignedUnit());
        $this->assertTrue(
            AppNotification::query()->where('user_id', $client->id)->where('type', 'application.approved')->exists()
        );

        $contract = $application->fresh()->contract;
        $this->assertNotNull($contract);
        $installment = $contract->installments()->where('number', 1)->firstOrFail();

        $voucher = $payments->submitVoucher($client, $installment, UploadedFile::fake()->image('pago.jpg'), [
            'paid_on' => now()->toDateString(),
            'reference' => 'REF-TEST-1',
        ]);

        $this->assertSame(VoucherStatus::Pending, $voucher->status);
        $this->assertSame(InstallmentStatus::Pending, $installment->fresh()->status);
        $this->assertNotNull($voucher->exchange_rate);
        $this->assertNotNull($voucher->amount_ves);
        $this->assertNotNull($voucher->rate_frozen_at);
        $frozenRate = (float) $voucher->exchange_rate;
        $frozenVes = (float) $voucher->amount_ves;
        $this->assertTrue(
            AppNotification::query()->where('user_id', $admin->id)->where('type', 'payment.submitted')->exists()
        );

        $this->actingAs($admin)
            ->put(route('admin.exchange-rate.update'), ['usd_ves_rate' => 1200])
            ->assertRedirect(route('admin.exchange-rate.edit'));
        $this->assertSame(1200.0, app(\App\Services\Settings\ExchangeRate::class)->current());

        $voucher->refresh();
        $this->assertSame($frozenRate, (float) $voucher->exchange_rate);
        $this->assertSame($frozenVes, (float) $voucher->amount_ves);

        $this->actingAs($admin)
            ->get(route('admin.payments.index', ['estado' => 'pendiente']))
            ->assertOk()
            ->assertSee('Validar')
            ->assertSee($client->name)
            ->assertSee('Tasa congelada');

        $this->actingAs($admin)
            ->get(route('admin.payments.show', $voucher))
            ->assertOk()
            ->assertSee('Tasa congelada')
            ->assertSee('Tasa vigente hoy');

        $payments->approve($admin, $voucher->fresh());

        $this->assertSame(VoucherStatus::Approved, $voucher->fresh()->status);
        $this->assertSame(InstallmentStatus::Paid, $installment->fresh()->status);
        $this->assertTrue(
            AppNotification::query()->where('user_id', $client->id)->where('type', 'payment.approved')->exists()
        );
        $this->assertTrue(
            AppNotification::query()->where('user_id', $admin->id)->where('type', 'payment.approved.admin')->exists()
        );
        $this->assertSame(0, PaymentVoucher::query()->where('user_id', $client->id)->where('status', VoucherStatus::Pending)->count());

        $this->actingAs($client)
            ->get(route('notifications.feed'))
            ->assertOk()
            ->assertJsonPath('unread', fn ($value) => $value >= 1);

        $this->actingAs($client)
            ->get(route('client.payments.vouchers'))
            ->assertOk()
            ->assertSee('Descargar PDF')
            ->assertSee('REF-TEST-1')
            ->assertSee('XY99ZZ');

        $this->actingAs($client)
            ->get(route('client.payments.voucher.pdf', $voucher))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_application_rejection_notifies_client_and_admins(): void
    {
        $this->seed();

        $client = User::query()->where('email', 'cliente@kadimotors.test')->firstOrFail();
        $admin = User::query()->where('email', 'admin@kadimotors.test')->firstOrFail();
        $branch = \App\Models\Branch::query()->where('city', 'Naguanagua')->firstOrFail();
        $motorcycle = Motorcycle::query()->where('slug', 'classic-150')->firstOrFail();

        $this->actingAs($client)->post(route('client.applications.store'), [
            'motorcycle_id' => $motorcycle->id,
            'branch_id' => $branch->id,
            'channel' => 'en_sede',
            'color' => 'Negro',
            'city' => 'Naguanagua',
            'whatsapp_phone' => '+58 412 0000000',
        ])->assertRedirect();

        $application = $client->applications()->latest('id')->firstOrFail();

        $this->actingAs($admin)->post(route('staff.applications.reject', $application), [
            'reason' => 'Documentación incompleta en carpeta.',
        ])->assertRedirect();

        $this->assertSame(ApplicationStatus::Rejected, $application->fresh()->status);
        $this->assertTrue(
            AppNotification::query()->where('user_id', $client->id)->where('type', 'application.rejected')->exists()
        );
    }
}
