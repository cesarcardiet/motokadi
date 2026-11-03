<?php

namespace App\Http\Controllers\Admin;

use App\Enums\VoucherStatus;
use App\Http\Controllers\Controller;
use App\Models\PaymentVoucher;
use App\Services\Applications\WorkflowException;
use App\Services\Financing\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('estado')->toString();
        if ($status === '') {
            $status = 'pendiente';
        }

        $query = PaymentVoucher::query()
            ->with(['client', 'installment.contract.motorcycle', 'reviewer'])
            ->latest();

        if ($status !== 'todos') {
            $query->where('status', $status);
        }

        return view('admin.payments.index', [
            'vouchers' => $query->paginate(12)->withQueryString(),
            'status' => $status,
            'pendingCount' => PaymentVoucher::query()->where('status', VoucherStatus::Pending)->count(),
        ]);
    }

    public function show(PaymentVoucher $voucher): View
    {
        $voucher->load(['client', 'installment.contract.motorcycle', 'installment.contract.branch', 'reviewer']);

        return view('admin.payments.show', compact('voucher'));
    }

    public function approve(Request $request, PaymentVoucher $voucher, PaymentService $payments): RedirectResponse
    {
        $data = $request->validate([
            'review_note' => ['nullable', 'string', 'max:180'],
        ]);

        try {
            $payments->approve($request->user(), $voucher, $data['review_note'] ?? null);
        } catch (WorkflowException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.payments.index')
            ->with('status', 'Pago aprobado. La cuota quedó marcada como pagada.');
    }

    public function reject(Request $request, PaymentVoucher $voucher, PaymentService $payments): RedirectResponse
    {
        $data = $request->validate([
            'review_note' => ['required', 'string', 'min:8', 'max:180'],
        ], [
            'review_note.required' => 'Indica por qué rechazas el comprobante.',
        ]);

        try {
            $payments->reject($request->user(), $voucher, $data['review_note']);
        } catch (WorkflowException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.payments.index')
            ->with('status', 'Comprobante rechazado. El cliente puede subir otro.');
    }
}
