<?php

namespace App\Http\Controllers\Api;

use App\Enums\DocumentStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ApplicationResource;
use App\Models\ApplicationDocument;
use App\Models\CreditApplication;
use App\Services\Applications\ApplicationWorkflow;
use App\Services\Applications\WorkflowException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffApplicationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasPermission('solicitudes.revisar'), 403);

        $apps = CreditApplication::query()
            ->forActor($request->user())
            ->with(['client', 'motorcycle', 'branch', 'documents', 'contract.motorcycle'])
            ->latest('id')
            ->paginate(20);

        return response()->json([
            'data' => ApplicationResource::collection($apps->items()),
            'meta' => [
                'current_page' => $apps->currentPage(),
                'last_page' => $apps->lastPage(),
                'total' => $apps->total(),
            ],
        ]);
    }

    public function show(Request $request, CreditApplication $application): ApplicationResource
    {
        $this->assertVisible($request, $application);

        return new ApplicationResource($application);
    }

    public function approve(Request $request, CreditApplication $application, ApplicationWorkflow $workflow): JsonResponse
    {
        abort_unless($request->user()->hasPermission('solicitudes.aprobar'), 403);
        $this->assertVisible($request, $application);

        $data = $request->validate([
            'unit_cartel' => ['required', 'string', 'min:3', 'max:80'],
            'unit_plate' => ['required', 'string', 'min:3', 'max:80'],
            'unit_serial' => ['required', 'string', 'min:3', 'max:120'],
        ]);

        try {
            $workflow->approve($request->user(), $application, $data);
        } catch (WorkflowException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Solicitud aprobada y moto asignada.',
            'data' => new ApplicationResource($application->fresh(['motorcycle', 'branch', 'documents', 'contract.motorcycle'])),
        ]);
    }

    public function verifyLocation(Request $request, CreditApplication $application, ApplicationWorkflow $workflow): JsonResponse
    {
        abort_unless($request->user()->hasPermission('solicitudes.verificar_ubicacion'), 403);
        $this->assertVisible($request, $application);

        $data = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'note' => ['required', 'string', 'min:8', 'max:240'],
        ]);

        try {
            $workflow->verifyLocation(
                $request->user(),
                $application,
                (float) $data['latitude'],
                (float) $data['longitude'],
                $data['note'],
            );
        } catch (WorkflowException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Dirección / visita confirmada.',
            'data' => new ApplicationResource($application->fresh(['motorcycle', 'branch', 'documents', 'contract.motorcycle'])),
        ]);
    }

    public function reviewDocument(
        Request $request,
        CreditApplication $application,
        ApplicationDocument $document,
        ApplicationWorkflow $workflow,
    ): JsonResponse {
        abort_unless($request->user()->hasPermission('solicitudes.revisar'), 403);
        $this->assertVisible($request, $application);
        abort_unless($document->credit_application_id === $application->id, 404);

        $data = $request->validate([
            'status' => ['required', 'in:aceptado,rechazado'],
            'review_note' => ['nullable', 'string', 'max:180'],
        ]);

        try {
            $workflow->reviewDocument(
                $request->user(),
                $document,
                DocumentStatus::from($data['status']),
                $data['review_note'] ?? null,
            );
        } catch (WorkflowException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Documento revisado.',
            'data' => new ApplicationResource($application->fresh(['motorcycle', 'branch', 'documents', 'contract.motorcycle'])),
        ]);
    }

    private function assertVisible(Request $request, CreditApplication $application): void
    {
        abort_unless($request->user()->hasPermission('solicitudes.revisar'), 403);
        $found = CreditApplication::query()->forActor($request->user())->whereKey($application->id)->exists();
        abort_unless($found, 403);
    }
}
