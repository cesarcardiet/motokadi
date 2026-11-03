<?php

namespace App\Http\Controllers\Client;

use App\Enums\DocumentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCreditApplicationRequest;
use App\Models\ApplicationDocument;
use App\Models\CreditApplication;
use App\Services\Applications\ApplicationWorkflow;
use App\Services\Applications\WorkflowException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $applications = $request->user()
            ->applications()
            ->with(['motorcycle', 'branch'])
            ->latest()
            ->paginate(8);

        return view('client.applications.index', compact('applications'));
    }

    public function create(Request $request): RedirectResponse
    {
        return redirect()->route('catalog.index', array_filter([
            'solicitar' => $request->string('moto')->toString(),
            'canal' => $request->string('canal')->toString(),
        ]));
    }

    public function store(StoreCreditApplicationRequest $request, ApplicationWorkflow $workflow): RedirectResponse
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
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('client.applications.show', $application)
            ->with('status', 'Solicitud enviada. El plan quedó congelado con los precios de hoy.');
    }

    public function show(Request $request, CreditApplication $application): View
    {
        abort_unless($application->user_id === $request->user()->id, 403);
        $application->load(['motorcycle', 'branch', 'documents', 'events.actor']);

        return view('client.applications.show', [
            'application' => $application,
        ]);
    }

    public function replaceDocument(
        Request $request,
        CreditApplication $application,
        ApplicationDocument $document,
        ApplicationWorkflow $workflow,
    ): RedirectResponse {
        abort_unless($document->credit_application_id === $application->id, 404);

        $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf,webp', 'max:5120'],
        ]);

        try {
            $workflow->replaceDocument($request->user(), $document, $request->file('file'));
        } catch (WorkflowException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('status', 'Documento actualizado. Queda otra vez en revisión.');
    }
}
