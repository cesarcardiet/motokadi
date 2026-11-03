<?php

namespace App\Http\Controllers;

use App\Enums\VoucherStatus;
use App\Models\AppNotification;
use App\Models\FinancingContract;
use App\Models\PaymentVoucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function feed(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $unread = AppNotification::query()
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->count();

        $latestId = (int) (AppNotification::query()
            ->where('user_id', $userId)
            ->max('id') ?? 0);

        $items = AppNotification::query()
            ->where('user_id', $userId)
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn (AppNotification $notification) => [
                'id' => $notification->id,
                'type' => $notification->type,
                'title' => $notification->title,
                'text' => $notification->body,
                'href' => $notification->link ?: url('/'),
                'unread' => $notification->read_at === null,
                'when' => $notification->created_at?->diffForHumans(),
            ])
            ->values();

        return response()->json([
            'unread' => $unread,
            'latest_id' => $latestId,
            'items' => $items,
            'payments_stamp' => $this->paymentsStamp($request),
        ]);
    }

    public function markRead(Request $request): JsonResponse
    {
        AppNotification::query()
            ->where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }

    private function paymentsStamp(Request $request): string
    {
        $user = $request->user();

        if ($user->hasPermission('panel.admin')) {
            $pending = PaymentVoucher::query()->where('status', VoucherStatus::Pending)->count();
            $maxId = (int) (PaymentVoucher::query()->max('id') ?? 0);
            $maxReviewed = (string) (PaymentVoucher::query()->max('reviewed_at') ?? '');
            $maxUpdated = (string) (PaymentVoucher::query()->max('updated_at') ?? '');

            return 'admin:'.$pending.':'.$maxId.':'.$maxReviewed.':'.$maxUpdated;
        }

        $contractIds = FinancingContract::query()
            ->where('user_id', $user->id)
            ->pluck('id');

        $voucherStamp = PaymentVoucher::query()
            ->where('user_id', $user->id)
            ->selectRaw("count(*) as total, coalesce(max(id),0) as max_id, coalesce(max(updated_at),'') as max_updated, group_concat(status || '-' || id) as states")
            ->first();

        $installmentStamp = \App\Models\Installment::query()
            ->whereIn('financing_contract_id', $contractIds)
            ->selectRaw("group_concat(id || ':' || status) as states, coalesce(max(updated_at),'') as max_updated")
            ->first();

        return 'client:'.($voucherStamp->total ?? 0).':'.($voucherStamp->max_id ?? 0).':'.($voucherStamp->states ?? '').':'.($installmentStamp->states ?? '').':'.($installmentStamp->max_updated ?? '');
    }
}
