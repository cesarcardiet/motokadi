<?php

namespace App\Http\Controllers\Staff;

use App\Enums\ApplicationStatus;
use App\Enums\DocumentStatus;
use App\Http\Controllers\Controller;
use App\Models\ApplicationDocument;
use App\Models\Branch;
use App\Models\CreditApplication;
use App\Services\Applications\ApplicationWorkflow;
use App\Services\Applications\WorkflowException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('estado')->toString();
        $query = CreditApplication::query()
            ->forActor($request->user())
            ->with(['client', 'motorcycle', 'branch'])
            ->latest();

        if ($status !== '') {
            $query->where('status', $status);
        }

        return view('staff.applications.index', [
            'applications' => $query->paginate(8)->withQueryString(),
            'status' => $status,
            'statuses' => ApplicationStatus::cases(),
            'branches' => Branch::query()->orderBy('name')->get(),
        ]);
    }

    public function show(Request $request, CreditApplication $application): View
    {
        $this->visible($request, $application);
        $application->load(['client', 'motorcycle', 'branch', 'documents.reviewer', 'events.actor']);

        return view('staff.applications.show', compact('application'));
    }

    public function preevaluate(Request $request, CreditApplication $application, ApplicationWorkflow $workflow): RedirectResponse
    {
        return $this->act($request, $application, fn () => $workflow->completePreevaluation($request->user(), $application));
    }

    public function reviewDocument(
        Request $request,
        CreditApplication $application,
        ApplicationDocument $document,
        ApplicationWorkflow $workflow,
    ): RedirectResponse {
        abort_unless($document->credit_application_id === $application->id, 404);
        $data = $request->validate([
            'status' => ['required', 'in:aceptado,rechazado'],
            'review_note' => ['nullable', 'string', 'max:180'],
        ]);

        return $this->act($request, $application, fn () => $workflow->reviewDocument(
            $request->user(),
            $document,
            DocumentStatus::from($data['status']),
            $data['review_note'] ?? null,
        ));
    }

    public function verifyPapers(Request $request, CreditApplication $application, ApplicationWorkflow $workflow): RedirectResponse
    {
        return $this->act($request, $application, fn () => $workflow->verifyPapers($request->user(), $application));
    }

    public function receiveFolder(Request $request, CreditApplication $application, ApplicationWorkflow $workflow): RedirectResponse
    {
        return $this->act($request, $application, fn () => $workflow->receiveFolder($request->user(), $application));
    }

    public function verifyLocation(Request $request, CreditApplication $application, ApplicationWorkflow $workflow): RedirectResponse
    {
        $data = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'note' => ['required', 'string', 'min:8', 'max:240'],
            'confirm' => ['accepted'],
        ], [
            'confirm.accepted' => 'Confirma que la ubicación coincide con el solicitante.',
            'note.min' => 'Describe cómo verificaste la ubicación.',
        ]);

        return $this->act($request, $application, fn () => $workflow->verifyLocation(
            $request->user(),
            $application,
            (float) $data['latitude'],
            (float) $data['longitude'],
            $data['note'],
        ));
    }

    public function approve(Request $request, CreditApplication $application, ApplicationWorkflow $workflow): RedirectResponse
    {
        return $this->act($request, $application, fn () => $workflow->approve($request->user(), $application));
    }

    public function reject(Request $request, CreditApplication $application, ApplicationWorkflow $workflow): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:8', 'max:240'],
        ]);

        return $this->act($request, $application, fn () => $workflow->reject($request->user(), $application, $data['reason']));
    }

    private function visible(Request $request, CreditApplication $application): void
    {
        abort_unless($request->user()->hasPermission('solicitudes.revisar'), 403);

        $found = CreditApplication::query()->forActor($request->user())->whereKey($application->id)->exists();
        abort_unless($found, 403);
    }

    private function act(Request $request, CreditApplication $application, callable $callback): RedirectResponse
    {
        $this->visible($request, $application);

        try {
            $callback();
        } catch (WorkflowException $exception) {
            return back()->with('error', $exception->getMessage());
        } catch (AuthorizationException $exception) {
            abort(403, $exception->getMessage());
        }

        return back()->with('status', 'La solicitud avanzó.');
    }
}
