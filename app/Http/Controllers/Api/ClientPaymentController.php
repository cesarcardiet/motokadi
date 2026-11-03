<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ContractResource;
use App\Models\FinancingContract;
use App\Models\Installment;
use App\Models\PaymentVoucher;
use App\Services\Applications\WorkflowException;
use App\Services\Financing\PaymentService;
use App\Services\Financing\PaymentVoucherPdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClientPaymentController extends Controller
{
    public function contracts(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasPermission('solicitudes.crear'), 403);

        $contracts = FinancingContract::query()
            ->where('user_id', $request->user()->id)
            ->with(['motorcycle', 'installments.vouchers'])
            ->latest('signed_at')
            ->get();

        return response()->json(['data' => ContractResource::collection($contracts)]);
    }

    public function vouchers(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasPermission('solicitudes.crear'), 403);

        $vouchers = PaymentVoucher::query()
            ->where('user_id', $request->user()->id)
            ->with(['installment.contract.motorcycle'])
            ->latest('id')
            ->get()
            ->map(fn (PaymentVoucher $voucher) => $this->voucherPayload($voucher));

        return response()->json(['data' => $vouchers]);
    }

    public function downloadPdf(Request $request, PaymentVoucher $voucher, PaymentVoucherPdf $pdf): Response|StreamedResponse
    {
        abort_unless($voucher->user_id === $request->user()->id, 403);

        return $pdf->download($voucher);
    }

    public function store(Request $request, Installment $installment, PaymentService $payments): JsonResponse
    {
        abort_unless($request->user()->hasPermission('solicitudes.crear'), 403);

        $data = $request->validate([
            'voucher' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'paid_on' => ['nullable', 'date', 'before_or_equal:today'],
            'reference' => ['nullable', 'string', 'max:80'],
        ]);

        try {
            $voucher = $payments->submitVoucher($request->user(), $installment, $request->file('voucher'), [
                'paid_on' => $data['paid_on'] ?? null,
                'reference' => $data['reference'] ?? null,
            ]);
        } catch (WorkflowException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Comprobante enviado. Queda en revisión.',
            'data' => $this->voucherPayload($voucher->fresh(['installment.contract.motorcycle'])),
        ], 201);
    }

    private function voucherPayload(PaymentVoucher $voucher): array
    {
        $contract = $voucher->installment?->contract;

        return [
            'id' => $voucher->id,
            'status' => $voucher->status->value,
            'status_label' => $voucher->status->label(),
            'amount' => (float) $voucher->amount,
            'amount_ves' => $voucher->amountInVes(),
            'exchange_rate' => $voucher->frozenRate(),
            'exchange_rate_label' => $voucher->frozenRateLabel(),
            'paid_on' => $voucher->paid_on?->toDateString(),
            'reference' => $voucher->reference,
            'original_name' => $voucher->original_name,
            'installment_number' => $voucher->installment?->number,
            'contract_number' => $contract?->contract_number,
            'motorcycle' => $contract?->motorcycle?->name,
            'unit' => $contract && $contract->hasAssignedUnit() ? [
                'cartel' => $contract->unit_cartel,
                'plate' => $contract->unit_plate,
                'serial' => $contract->unit_serial,
            ] : null,
            'pdf_url' => url('/api/v1/vouchers/'.$voucher->id.'/pdf'),
            'created_at' => $voucher->created_at?->toIso8601String(),
        ];
    }
}
