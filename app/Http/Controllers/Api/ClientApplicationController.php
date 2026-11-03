<?php

namespace App\Http\Controllers\Api;

use App\Enums\DocumentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCreditApplicationRequest;
use App\Http\Resources\Api\ApplicationResource;
use App\Models\ApplicationDocument;
use App\Models\CreditApplication;
use App\Services\Applications\ApplicationWorkflow;
use App\Services\Applications\WorkflowException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientApplicationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasPermission('solicitudes.crear'), 403);

        $apps = CreditApplication::query()
            ->where('user_id', $request->user()->id)
            ->with(['motorcycle', 'branch', 'documents', 'contract.motorcycle'])
            ->latest('id')
            ->get();

        return response()->json(['data' => ApplicationResource::collection($apps)]);
    }

    public function show(Request $request, CreditApplication $application): ApplicationResource
    {
        abort_unless($request->user()->hasPermission('solicitudes.crear'), 403);
        abort_unless($application->user_id === $request->user()->id, 403);

        return new ApplicationResource($application);
    }

    public function store(StoreCreditApplicationRequest $request, ApplicationWorkflow $workflow): JsonResponse
    {
        $uploads = [];
        foreach (DocumentType::required() as $type) {
            if ($request->hasFile('documents.'.$type->value)) {
                $uploads[$type->value] = $request->file('documents.'.$type->value);
            }
        }

        try {
            $application = $workflow->open($request->user(), $request->validated(), $uploads);
        } catch (WorkflowException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Solicitud creada.',
            'data' => new ApplicationResource($application->fresh(['motorcycle', 'branch', 'documents', 'contract.motorcycle'])),
        ], 201);
    }

    public function replaceDocument(
        Request $request,
        CreditApplication $application,
        ApplicationDocument $document,
        ApplicationWorkflow $workflow,
    ): JsonResponse {
        abort_unless($request->user()->hasPermission('solicitudes.crear'), 403);
        abort_unless($application->user_id === $request->user()->id, 403);
        abort_unless($document->credit_application_id === $application->id, 404);

        $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf,webp', 'max:5120'],
        ]);

        try {
            $workflow->replaceDocument($request->user(), $document, $request->file('file'));
        } catch (WorkflowException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Documento actualizado.',
            'data' => new ApplicationResource($application->fresh(['motorcycle', 'branch', 'documents', 'contract.motorcycle'])),
        ]);
    }
}
