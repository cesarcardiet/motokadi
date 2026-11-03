<?php

namespace App\Services\Applications;

use App\Enums\ApplicationChannel;
use App\Enums\ApplicationStatus;
use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Models\ApplicationDocument;
use App\Models\ApplicationEvent;
use App\Models\Branch;
use App\Models\CreditApplication;
use App\Models\Motorcycle;
use App\Models\User;
use App\Services\Financing\ContractService;
use App\Services\Financing\InstallmentSimulator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Access\AuthorizationException;

class ApplicationWorkflow
{
    public function __construct(
        private readonly InstallmentSimulator $simulator,
        private readonly ApplicationNotifier $notifier,
        private readonly ContractService $contracts,
    ) {
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, UploadedFile>  $uploads
     */
    public function open(User $client, array $attributes, array $uploads): CreditApplication
    {
        if ($client->hasActiveApplication()) {
            throw new WorkflowException('Tienes una solicitud activa, espera la aprobación.');
        }

        $application = DB::transaction(function () use ($client, $attributes, $uploads) {
            $motorcycle = Motorcycle::query()->where('is_active', true)->findOrFail($attributes['motorcycle_id']);
            $branch = Branch::query()->where('is_active', true)->findOrFail($attributes['branch_id']);

            if (! in_array($attributes['color'], $motorcycle->colors, true)) {
                throw new WorkflowException('Ese color no está disponible para este modelo.');
            }

            $channel = ApplicationChannel::from($attributes['channel']);

            if ($channel === ApplicationChannel::Online) {
                foreach (DocumentType::required() as $type) {
                    if (! isset($uploads[$type->value])) {
                        throw new WorkflowException('Falta el documento: '.$type->label().'.');
                    }
                }
            }

            $quote = $this->simulator->quote(
                $motorcycle->price,
                $motorcycle->initial_payment,
                $motorcycle->term_months,
            );
            $status = $channel === ApplicationChannel::Online
                ? ApplicationStatus::Preevaluation
                : ApplicationStatus::Papers;

            $application = CreditApplication::query()->create([
                'user_id' => $client->id,
                'motorcycle_id' => $motorcycle->id,
                'branch_id' => $branch->id,
                'channel' => $channel,
                'status' => $status,
                'color' => $attributes['color'],
                'price' => $quote['price'],
                'initial_payment' => $quote['initial'],
                'balance' => $quote['balance'],
                'monthly_payment' => $quote['monthly'],
                'term_months' => $quote['months'],
                'address' => ($attributes['address'] ?? '') !== ''
                    ? $attributes['address']
                    : 'Se confirma al recibir la carpeta en sede',
                'city' => $attributes['city'],
                'whatsapp_phone' => $attributes['whatsapp_phone'] ?? null,
                'latitude' => $attributes['latitude'] ?? null,
                'longitude' => $attributes['longitude'] ?? null,
                'submitted_at' => now(),
            ]);

            foreach (DocumentType::required() as $type) {
                if (! isset($uploads[$type->value])) {
                    continue;
                }

                $file = $uploads[$type->value];
                $application->documents()->create([
                    'type' => $type,
                    'path' => $file->store('solicitudes/'.$application->id, 'public'),
                    'original_name' => $file->getClientOriginalName(),
                    'status' => DocumentStatus::Pending,
                ]);
            }

            $this->record(
                $application,
                $client,
                null,
                $status,
                $channel === ApplicationChannel::Online
                    ? 'Documentos cargados. Queda en espera de la llamada de preevaluación.'
                    : 'El cliente llevará la carpeta con los requisitos a la sede.',
            );

            return $application;
        });

        $this->notifier->created($application);

        return $application;
    }

    public function replaceDocument(User $client, ApplicationDocument $document, UploadedFile $file): void
    {
        $application = $document->application;

        if ($application->user_id !== $client->id) {
            throw new AuthorizationException('Solo el solicitante puede reemplazar sus documentos.');
        }

        if (! $application->status->isOpen()) {
            throw new WorkflowException('Esta solicitud ya está cerrada.');
        }

        if (! in_array($application->status, [ApplicationStatus::Preevaluation, ApplicationStatus::Papers], true)) {
            throw new WorkflowException('Los documentos ya no se pueden cambiar en esta etapa.');
        }

        $document->update([
            'path' => $file->store('solicitudes/'.$application->id, 'public'),
            'original_name' => $file->getClientOriginalName(),
            'status' => DocumentStatus::Pending,
            'review_note' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);

        $this->record($application, $client, $application->status, $application->status, 'Documento reemplazado: '.$document->type->label().'.');
    }

    public function reviewDocument(User $actor, ApplicationDocument $document, DocumentStatus $status, ?string $note): void
    {
        $application = $document->application;
        $this->assertActor($actor, $application, 'solicitudes.revisar');

        if (! in_array($application->status, [ApplicationStatus::Preevaluation, ApplicationStatus::Papers], true)) {
            throw new WorkflowException('Los papeles ya no están en revisión.');
        }

        if ($status === DocumentStatus::Pending) {
            throw new WorkflowException('Elige si el documento se acepta o se rechaza.');
        }

        $document->update([
            'status' => $status,
            'review_note' => $note,
            'reviewed_by' => $actor->id,
            'reviewed_at' => now(),
        ]);

        $this->record(
            $application,
            $actor,
            $application->status,
            $application->status,
            $document->type->label().' marcado como '.$status->label().'.',
        );
    }

    public function completePreevaluation(User $actor, CreditApplication $application): void
    {
        $this->assertActor($actor, $application, 'solicitudes.preevaluar');
        $this->assertStatus($application, ApplicationStatus::Preevaluation);
        $this->assertNoRejectedDocuments($application);

        $this->move($application, $actor, ApplicationStatus::Papers, 'Preevaluación aceptada. Los papeles pasan a la sede.');
    }

    public function receiveFolder(User $actor, CreditApplication $application): void
    {
        $this->assertActor($actor, $application, 'solicitudes.verificar_papeles');
        $this->assertStatus($application, ApplicationStatus::Papers);

        if ($application->documents()->count() === 0) {
            foreach (DocumentType::required() as $type) {
                $application->documents()->create([
                    'type' => $type,
                    'path' => '',
                    'original_name' => 'Entregado en sede',
                    'status' => DocumentStatus::Accepted,
                    'reviewed_by' => $actor->id,
                    'reviewed_at' => now(),
                ]);
            }
        }

        $this->verifyPapers($actor, $application->fresh());
    }

    public function verifyPapers(User $actor, CreditApplication $application): void
    {
        $this->assertActor($actor, $application, 'solicitudes.verificar_papeles');
        $this->assertStatus($application, ApplicationStatus::Papers);

        $application->load('documents');
        $accepted = $application->documents->every(
            fn (ApplicationDocument $document) => $document->status === DocumentStatus::Accepted
        );

        if ($application->documents->count() < count(DocumentType::required()) || ! $accepted) {
            throw new WorkflowException('Todos los recaudos deben estar aceptados antes de pasar a la ubicación.');
        }

        $this->move($application, $actor, ApplicationStatus::Location, 'Papeles verificados. Sigue la visita a domicilio / prueba de vista.');
    }

    public function verifyLocation(User $actor, CreditApplication $application, float $latitude, float $longitude, string $note): void
    {
        $this->assertActor($actor, $application, 'solicitudes.verificar_ubicacion');
        $this->assertStatus($application, ApplicationStatus::Location);

        $application->latitude = $latitude;
        $application->longitude = $longitude;
        $application->location_verified_at = now();
        $application->save();

        $this->move(
            $application,
            $actor,
            ApplicationStatus::Preapproved,
            'Visita / prueba de vista confirmada ('.$latitude.', '.$longitude.'). '.$note,
        );
    }

    /**
     * @param  array{unit_cartel: string, unit_plate: string, unit_serial: string}  $unit
     */
    public function approve(User $actor, CreditApplication $application, array $unit): void
    {
        $this->assertActor($actor, $application, 'solicitudes.aprobar');
        $this->assertStatus($application, ApplicationStatus::Preapproved);

        $cartel = trim($unit['unit_cartel']);
        $plate = trim($unit['unit_plate']);
        $serial = trim($unit['unit_serial']);

        if ($cartel === '' || $plate === '' || $serial === '') {
            throw new WorkflowException('Para aprobar debes asignar la moto con cartel, placa y serial.');
        }

        $this->move(
            $application,
            $actor,
            ApplicationStatus::Approved,
            'Aprobación final. Moto asignada: cartel '.$cartel.', placa '.$plate.', serial '.$serial.'.',
        );
        $fresh = $application->fresh(['client', 'motorcycle', 'branch']);
        $this->contracts->activateFromApproval($fresh, null, [
            'unit_cartel' => $cartel,
            'unit_plate' => $plate,
            'unit_serial' => $serial,
        ]);
        $this->notifier->approved($fresh);
    }

    public function reject(User $actor, CreditApplication $application, string $reason): void
    {
        $this->assertActor($actor, $application, 'solicitudes.rechazar');

        if (! $application->status->isOpen()) {
            throw new WorkflowException('Esta solicitud ya está cerrada.');
        }

        $application->rejection_reason = $reason;
        $application->save();
        $this->move($application, $actor, ApplicationStatus::Rejected, $reason);
        $this->notifier->rejected($application->fresh(['client', 'motorcycle', 'branch']), $reason);
    }

    private function assertStatus(CreditApplication $application, ApplicationStatus $expected): void
    {
        if ($application->status !== $expected) {
            throw new WorkflowException('La solicitud no está en '.$expected->label().'.');
        }
    }

    private function assertActor(User $actor, CreditApplication $application, string $permission): void
    {
        if (! $actor->hasPermission($permission)) {
            throw new AuthorizationException('No tienes permiso para esta acción.');
        }

        if (! $actor->hasPermission('panel.admin') && $actor->branch_id !== $application->branch_id) {
            throw new AuthorizationException('Esta solicitud pertenece a otra sede.');
        }
    }

    private function assertNoRejectedDocuments(CreditApplication $application): void
    {
        $application->load('documents');
        $types = $application->documents->map(fn (ApplicationDocument $document) => $document->type->value)->all();

        foreach (DocumentType::required() as $type) {
            if (! in_array($type->value, $types, true)) {
                throw new WorkflowException('Falta cargar '.$type->label().'.');
            }
        }

        if ($application->documents->contains(fn (ApplicationDocument $document) => $document->status === DocumentStatus::Rejected)) {
            throw new WorkflowException('Hay un documento rechazado. El solicitante debe reemplazarlo.');
        }
    }

    private function move(CreditApplication $application, User $actor, ApplicationStatus $to, string $note): void
    {
        $from = $application->status;
        $application->status = $to;
        $application->save();
        $this->record($application, $actor, $from, $to, $note);
    }

    private function record(
        CreditApplication $application,
        ?User $actor,
        ?ApplicationStatus $from,
        ?ApplicationStatus $to,
        string $note,
    ): void {
        ApplicationEvent::query()->create([
            'credit_application_id' => $application->id,
            'user_id' => $actor?->id,
            'action' => $to?->value ?? 'nota',
            'from_status' => $from,
            'to_status' => $to,
            'note' => $note,
        ]);
    }
}
