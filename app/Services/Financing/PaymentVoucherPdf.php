<?php

namespace App\Services\Financing;

use App\Models\PaymentVoucher;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentVoucherPdf
{
    public function download(PaymentVoucher $voucher): Response|StreamedResponse
    {
        $voucher->loadMissing([
            'client',
            'installment.contract.motorcycle',
            'installment.contract.branch',
            'reviewer',
        ]);

        $contract = $voucher->installment->contract;
        $filename = sprintf(
            'kadi-comprobante-%s-cuota-%02d.pdf',
            $contract->contract_number,
            $voucher->installment->number,
        );

        return Pdf::loadView('pdfs.payment-voucher', [
            'voucher' => $voucher,
            'contract' => $contract,
            'motorcycle' => $contract->motorcycle,
            'client' => $voucher->client,
            'installment' => $voucher->installment,
        ])
            ->setPaper('letter')
            ->download($filename);
    }
}
