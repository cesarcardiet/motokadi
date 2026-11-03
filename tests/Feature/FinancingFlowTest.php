<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Models\AppNotification;
use App\Models\Branch;
use App\Models\CreditApplication;
use App\Models\Motorcycle;
use App\Models\User;
use App\Services\Applications\ApplicationWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FinancingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_shows_the_example_installments(): void
    {
        $this->seed();

        $this->get('/')->assertOk()->assertSee('Furioso 250');

        $catalog = $this->get('/catalogo')->assertOk()->assertSee('Siguiente');
        $this->assertSame(8, substr_count($catalog->getContent(), 'data-open-online='));
        $this->assertSame(8, substr_count($catalog->getContent(), 'data-open-persona='));

        $pageTwo = $this->get('/catalogo?page=2')->assertOk()->assertSee('Anterior');
        $this->assertSame(8, substr_count($pageTwo->getContent(), 'data-open-online='));
        $pageTwo->assertSee('data-open-online="carga-250"', false);

        $cargo = $this->get('/catalogo?linea=carguero')->assertOk();
        $this->assertSame(3, substr_count($cargo->getContent(), 'data-open-online='));

        $this->get('/simulacion')
            ->assertOk()
            ->assertSee('Kadi Classic 150')
            ->assertSee('$74 USD')
            ->assertSee('$100 USD')
            ->assertSee('$108 USD');

        $this->assertSame(16, Motorcycle::query()->count());
    }

    public function test_branch_folder_opens_without_uploads_and_seller_receives_it(): void
    {
        $this->seed();

        $client = User::query()->where('email', 'cliente@kadimotors.test')->firstOrFail();
        $seller = User::query()->where('email', 'vendedor.naguanagua@kadimotors.test')->firstOrFail();
        $motorcycle = Motorcycle::query()->where('slug', 'classic-150')->firstOrFail();
        $branch = Branch::query()->where('city', 'Naguanagua')->firstOrFail();

        $this->actingAs($client)->post(route('client.applications.store'), [
            'motorcycle_id' => $motorcycle->id,
            'branch_id' => $branch->id,
            'channel' => 'en_sede',
            'color' => 'Negro',
            'city' => 'Naguanagua',
            'whatsapp_phone' => '+58 412 0000000',
        ])->assertRedirect();

        $application = CreditApplication::query()->latest('id')->firstOrFail();
        $this->assertSame(ApplicationStatus::Papers, $application->status);
        $this->assertSame(0, $application->documents()->count());

        $this->actingAs($seller)
            ->post(route('staff.applications.folder', $application))
            ->assertRedirect();

        $application->refresh();
        $this->assertSame(ApplicationStatus::Location, $application->status);
        $this->assertSame(count(DocumentType::cases()), $application->documents()->count());
        $this->assertTrue($application->documents->every(
            fn ($document) => $document->status === DocumentStatus::Accepted && $document->path === ''
        ));
    }

    public function test_online_request_is_preapproved_only_after_location_and_closed_by_admin(): void
    {
        $this->seed();
        Storage::fake('public');

        $client = User::query()->where('email', 'cliente@kadimotors.test')->firstOrFail();
        $seller = User::query()->where('email', 'vendedor.naguanagua@kadimotors.test')->firstOrFail();
        $admin = User::query()->where('email', 'admin@kadimotors.test')->firstOrFail();
        $motorcycle = Motorcycle::query()->where('slug', 'classic-150')->firstOrFail();
        $branch = Branch::query()->where('city', 'Naguanagua')->firstOrFail();

        $uploads = [];
        foreach (DocumentType::cases() as $type) {
            $uploads[$type->value] = UploadedFile::fake()->create($type->value.'.pdf', 20, 'application/pdf');
        }

        $workflow = app(ApplicationWorkflow::class);
        $application = $workflow->open($client, [
            'motorcycle_id' => $motorcycle->id,
            'branch_id' => $branch->id,
            'channel' => 'en_linea',
            'color' => 'Negro',
            'address' => 'Av. Universidad',
            'city' => 'Naguanagua',
            'whatsapp_phone' => '+58 412 0000000',
            'latitude' => 10.2501,
            'longitude' => -68.0101,
        ], $uploads);

        $this->assertSame(ApplicationStatus::Preevaluation, $application->status);
        $this->assertSame('74.44', $application->monthly_payment);

        $workflow->completePreevaluation($seller, $application->fresh());
        $application->refresh()->load('documents');

        foreach ($application->documents as $document) {
            $workflow->reviewDocument($seller, $document, DocumentStatus::Accepted, null);
        }

        $workflow->verifyPapers($seller, $application->fresh());
        $workflow->verifyLocation($seller, $application->fresh(), 10.2501, -68.0101, 'GPS coincide con la casa del solicitante.');
        $application->refresh();

        $this->assertSame(ApplicationStatus::Preapproved, $application->status);
        $this->assertNotNull($application->location_verified_at);

        $unit = [
            'unit_cartel' => 'CT-7788',
            'unit_plate' => 'AB12CD',
            'unit_serial' => 'KADI7788CHS001',
        ];

        $this->actingAs($seller)
            ->post(route('staff.applications.approve', $application), $unit)
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('staff.applications.approve', $application))
            ->assertSessionHasErrors(['unit_cartel', 'unit_plate', 'unit_serial']);

        $this->actingAs($admin)
            ->post(route('staff.applications.approve', $application), $unit)
            ->assertRedirect();

        $application->refresh()->load('contract.installments');
        $this->assertSame(ApplicationStatus::Approved, $application->status);
        $this->assertNotNull($application->contract);
        $this->assertTrue($application->contract->hasAssignedUnit());
        $this->assertSame('CT-7788', $application->contract->unit_cartel);
        $this->assertSame('AB12CD', $application->contract->unit_plate);
        $this->assertSame('KADI7788CHS001', $application->contract->unit_serial);
        $this->assertSame(9, $application->contract->installments->count());
        $this->assertTrue(
            AppNotification::query()
                ->where('user_id', $client->id)
                ->where('type', 'application.approved')
                ->exists()
        );
        $this->assertSame(0, CreditApplication::query()->where('status', ApplicationStatus::Approved->value)->where('monthly_payment', '!=', '74.44')->whereKey($application->id)->count());
    }

    public function test_client_cannot_open_second_active_application_and_webhook_fires(): void
    {
        $this->seed();
        Storage::fake('public');
        Http::fake();
        config([
            'services.notifications.webhook_url' => 'https://hooks.example.test/kadi',
            'services.notifications.webhook_secret' => 'secret',
        ]);

        $client = User::query()->where('email', 'cliente@kadimotors.test')->firstOrFail();
        $motorcycle = Motorcycle::query()->where('slug', 'classic-150')->firstOrFail();
        $branch = Branch::query()->where('city', 'Naguanagua')->firstOrFail();

        $this->actingAs($client)->post(route('client.applications.store'), [
            'motorcycle_id' => $motorcycle->id,
            'branch_id' => $branch->id,
            'channel' => 'en_sede',
            'color' => 'Negro',
            'city' => 'Naguanagua',
            'whatsapp_phone' => '+58 412 0000000',
        ])->assertRedirect();

        $this->assertSame(1, CreditApplication::query()->where('user_id', $client->id)->count());

        Http::assertSent(fn ($request) => $request->url() === 'https://hooks.example.test/kadi'
            && $request['event'] === 'application.created');

        $this->actingAs($client)->post(route('client.applications.store'), [
            'motorcycle_id' => $motorcycle->id,
            'branch_id' => $branch->id,
            'channel' => 'en_sede',
            'color' => 'Negro',
            'city' => 'Naguanagua',
            'whatsapp_phone' => '+58 412 0000000',
        ])->assertRedirect()->assertSessionHas('error', 'Tienes una solicitud activa, espera la aprobación.');

        $this->assertSame(1, CreditApplication::query()->where('user_id', $client->id)->count());

        $this->actingAs($client)
            ->get(route('catalog.show', $motorcycle))
            ->assertOk()
            ->assertSee('Tienes una solicitud activa, espera la aprobación');
    }

    public function test_seller_cannot_open_another_branch_or_the_admin_panel(): void
    {
        $this->seed();

        $seller = User::query()->where('email', 'vendedor.naguanagua@kadimotors.test')->firstOrFail();
        $client = User::query()->where('email', 'cliente@kadimotors.test')->firstOrFail();
        $otherBranch = Branch::query()->where('city', 'Barquisimeto')->firstOrFail();
        $motorcycle = Motorcycle::query()->firstOrFail();

        $foreign = CreditApplication::query()->create([
            'user_id' => $client->id,
            'motorcycle_id' => $motorcycle->id,
            'branch_id' => $otherBranch->id,
            'channel' => 'en_sede',
            'status' => ApplicationStatus::Papers,
            'color' => 'Negro',
            'price' => '950.00',
            'initial_payment' => '280.00',
            'balance' => '670.00',
            'monthly_payment' => '74.44',
            'term_months' => 9,
            'address' => 'Calle 46',
            'city' => 'Barquisimeto',
            'latitude' => 10.06,
            'longitude' => -69.33,
            'submitted_at' => now(),
        ]);

        $this->actingAs($seller)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($seller)->get(route('staff.applications.show', $foreign))->assertForbidden();
        $this->actingAs($seller)
            ->get(route('staff.applications.index'))
            ->assertOk()
            ->assertSee('Activas')
            ->assertSee('No hay solicitudes en esta bandeja.')
            ->assertDontSee(route('staff.applications.show', $foreign), false);

        $this->actingAs($admin = User::query()->where('email', 'admin@kadimotors.test')->firstOrFail())
            ->get(route('staff.applications.index', ['estado' => 'activas']))
            ->assertOk()
            ->assertSee($foreign->client->name);
    }
}
