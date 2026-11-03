<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Enums\VoucherStatus;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\CreditApplication;
use App\Models\Motorcycle;
use App\Models\PaymentVoucher;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $counts = CreditApplication::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.dashboard', [
            'counts' => $counts,
            'activeCount' => CreditApplication::query()->active()->count(),
            'pendingPayments' => PaymentVoucher::query()->where('status', VoucherStatus::Pending)->count(),
            'statuses' => ApplicationStatus::cases(),
            'branches' => Branch::query()->count(),
            'sellers' => User::query()->whereHas('role', fn ($query) => $query->where('slug', 'vendedor'))->count(),
            'motorcycles' => Motorcycle::query()->where('is_active', true)->count(),
            'recent' => CreditApplication::query()->with(['client', 'motorcycle', 'branch'])->latest()->limit(6)->get(),
            'recentVouchers' => PaymentVoucher::query()
                ->with(['client', 'installment.contract.motorcycle'])
                ->where('status', VoucherStatus::Pending)
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }
}
