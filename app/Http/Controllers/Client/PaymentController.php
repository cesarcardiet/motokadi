<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\FinancingContract;
use App\Models\Installment;
use App\Models\PaymentVoucher;
use App\Services\Applications\WorkflowException;
use App\Services\Financing\PaymentService;
use App\Services\Financing\PaymentVoucherPdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $contracts = FinancingContract::query()
            ->where('user_id', $request->user()->id)
            ->with(['motorcycle', 'installments.vouchers'])
            ->latest('signed_at')
            ->get();

        return view('client.payments.index', compact('contracts'));
    }

    public function vouchers(Request $request): View
    {
        $vouchers = PaymentVoucher::query()
            ->where('user_id', $request->user()->id)
            ->with(['installment.contract.motorcycle'])
            ->latest()
            ->get();

        return view('client.payments.vouchers', compact('vouchers'));
    }

    public function downloadPdf(
        Request $request,
        PaymentVoucher $voucher,
        PaymentVoucherPdf $pdf,
    ): Response|StreamedResponse {
        abort_unless($voucher->user_id === $request->user()->id, 403);

        return $pdf->download($voucher);
    }

    public function store(Request $request, Installment $installment, PaymentService $payments): RedirectResponse
    {
        $data = $request->validate([
            'voucher' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'paid_on' => ['nullable', 'date', 'before_or_equal:today'],
            'reference' => ['nullable', 'string', 'max:80'],
        ], [
            'voucher.required' => 'Sube la foto o PDF del comprobante.',
        ]);

        try {
            $payments->submitVoucher($request->user(), $installment, $request->file('voucher'), [
                'paid_on' => $data['paid_on'] ?? null,
                'reference' => $data['reference'] ?? null,
            ]);
        } catch (WorkflowException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('client.payments.vouchers')
            ->with('status', 'Comprobante enviado. Ya puedes descargar el voucher PDF; el admin aún debe validar el pago.');
    }
}
