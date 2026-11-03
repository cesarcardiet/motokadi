<?php

namespace App\Services\Financing;

use App\Enums\InstallmentStatus;
use App\Enums\VoucherStatus;
use App\Models\AppNotification;
use App\Models\Installment;
use App\Models\PaymentVoucher;
use App\Models\User;
use App\Services\Applications\WorkflowException;
use App\Services\Settings\ExchangeRate;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(private readonly ExchangeRate $rates) {}

    /**
     * @param  array{paid_on?: string|null, reference?: string|null}  $meta
     */
    public function submitVoucher(User $client, Installment $installment, UploadedFile $file, array $meta = []): PaymentVoucher
    {
        $installment->loadMissing('contract');

        if ($installment->contract->user_id !== $client->id) {
            throw new WorkflowException('Esa cuota no pertenece a tu contrato.');
        }

        if ($installment->status === InstallmentStatus::Paid) {
            throw new WorkflowException('Esa cuota ya está marcada como pagada.');
        }

        if ($installment->vouchers()->where('status', VoucherStatus::Pending)->exists()) {
            throw new WorkflowException('Ya tienes un comprobante en revisión para esta cuota.');
        }

        $rate = $this->rates->current();
        $amountUsd = (float) $installment->amount;
        $frozenAt = now();

        $voucher = PaymentVoucher::query()->create([
            'installment_id' => $installment->id,
            'user_id' => $client->id,
            'path' => $file->store('comprobantes/'.$installment->financing_contract_id, 'public'),
            'original_name' => $file->getClientOriginalName(),
            'amount' => $amountUsd,
            'exchange_rate' => $rate,
            'amount_ves' => $this->rates->toVes($amountUsd, $rate),
            'rate_frozen_at' => $frozenAt,
            'paid_on' => $meta['paid_on'] ?? $frozenAt->toDateString(),
            'reference' => $meta['reference'] ?? null,
            'status' => VoucherStatus::Pending,
        ]);

        AppNotification::query()->create([
            'user_id' => $client->id,
            'type' => 'payment.submitted',
            'title' => 'Comprobante enviado',
            'body' => 'Tu comprobante de la cuota #'.$installment->number.' quedó en revisión.',
            'link' => route('client.payments.vouchers'),
        ]);

        User::query()
            ->whereHas('role.permissions', fn ($query) => $query->where('slug', 'panel.admin'))
            ->each(function (User $admin) use ($client, $installment, $voucher) {
                AppNotification::query()->create([
                    'user_id' => $admin->id,
                    'type' => 'payment.submitted',
                    'title' => 'Nuevo comprobante',
                    'body' => $client->name.' subió pago de la cuota #'.$installment->number.'.',
                    'link' => route('admin.payments.show', $voucher),
                ]);
            });

        return $voucher;
    }

    public function approve(User $admin, PaymentVoucher $voucher, ?string $note = null): void
    {
        if ($voucher->status !== VoucherStatus::Pending) {
            throw new WorkflowException('Este comprobante ya fue revisado.');
        }

        DB::transaction(function () use ($admin, $voucher, $note) {
            $voucher->update([
                'status' => VoucherStatus::Approved,
                'review_note' => $note,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            $installment = $voucher->installment()->lockForUpdate()->firstOrFail();
            $installment->update([
                'status' => InstallmentStatus::Paid,
                'paid_at' => $voucher->paid_on?->startOfDay() ?? now(),
            ]);
        });

        $voucher->loadMissing(['installment', 'client']);

        AppNotification::query()->create([
            'user_id' => $voucher->user_id,
            'type' => 'payment.approved',
            'title' => 'Pago aprobado',
            'body' => 'Tu pago de la cuota #'.$voucher->installment->number.' fue aprobado.',
            'link' => route('client.payments.vouchers'),
        ]);

        AppNotification::query()->create([
            'user_id' => $admin->id,
            'type' => 'payment.approved.admin',
            'title' => 'Pago validado',
            'body' => 'Aprobaste el pago de '.($voucher->client?->name ?? 'cliente').' · cuota #'.$voucher->installment->number.'.',
            'link' => route('admin.payments.index'),
        ]);
    }

    public function reject(User $admin, PaymentVoucher $voucher, string $reason): void
    {
        if ($voucher->status !== VoucherStatus::Pending) {
            throw new WorkflowException('Este comprobante ya fue revisado.');
        }

        $voucher->update([
            'status' => VoucherStatus::Rejected,
            'review_note' => $reason,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        $voucher->loadMissing(['installment', 'client']);

        AppNotification::query()->create([
            'user_id' => $voucher->user_id,
            'type' => 'payment.rejected',
            'title' => 'Comprobante rechazado',
            'body' => 'Tu pago de la cuota #'.$voucher->installment->number.' fue rechazado: '.$reason,
            'link' => route('client.payments.index'),
        ]);

        AppNotification::query()->create([
            'user_id' => $admin->id,
            'type' => 'payment.rejected.admin',
            'title' => 'Comprobante rechazado',
            'body' => 'Rechazaste el pago de '.($voucher->client?->name ?? 'cliente').' · cuota #'.$voucher->installment->number.'.',
            'link' => route('admin.payments.index'),
        ]);
    }
}
