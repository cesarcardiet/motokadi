<?php

namespace App\Http\Controllers\Api;

use App\Enums\VoucherStatus;
use App\Http\Controllers\Controller;
use App\Models\PaymentVoucher;
use App\Services\Applications\WorkflowException;
use App\Services\Financing\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasPermission('panel.admin'), 403);

        $status = $request->string('estado')->toString() ?: 'pendiente';
        $query = PaymentVoucher::query()
            ->with(['client', 'installment.contract.motorcycle'])
            ->latest('id');

        if ($status === 'pendiente') {
            $query->where('status', VoucherStatus::Pending);
        } elseif ($status === 'aprobado') {
            $query->where('status', VoucherStatus::Approved);
        } elseif ($status === 'rechazado') {
            $query->where('status', VoucherStatus::Rejected);
        }

        $rows = $query->paginate(20);

        return response()->json([
            'data' => collect($rows->items())->map(fn (PaymentVoucher $voucher) => [
                'id' => $voucher->id,
                'status' => $voucher->status->value,
                'status_label' => $voucher->status->label(),
                'amount' => (float) $voucher->amount,
                'reference' => $voucher->reference,
                'client' => $voucher->client?->only(['id', 'name', 'email']),
                'contract_number' => $voucher->installment?->contract?->contract_number,
                'motorcycle' => $voucher->installment?->contract?->motorcycle?->name,
                'installment_number' => $voucher->installment?->number,
                'created_at' => $voucher->created_at?->toIso8601String(),
            ])->values(),
            'meta' => [
                'current_page' => $rows->currentPage(),
                'last_page' => $rows->lastPage(),
                'total' => $rows->total(),
            ],
        ]);
    }

    public function approve(Request $request, PaymentVoucher $voucher, PaymentService $payments): JsonResponse
    {
        abort_unless($request->user()->hasPermission('panel.admin'), 403);

        $data = $request->validate([
            'review_note' => ['nullable', 'string', 'max:240'],
        ]);

        try {
            $payments->approve($request->user(), $voucher, $data['review_note'] ?? null);
        } catch (WorkflowException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['message' => 'Pago aprobado.']);
    }

    public function reject(Request $request, PaymentVoucher $voucher, PaymentService $payments): JsonResponse
    {
        abort_unless($request->user()->hasPermission('panel.admin'), 403);

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:240'],
        ]);

        try {
            $payments->reject($request->user(), $voucher, $data['reason']);
        } catch (WorkflowException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['message' => 'Pago rechazado.']);
    }
}
