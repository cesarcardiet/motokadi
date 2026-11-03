<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\CreditApplication;
use App\Models\Motorcycle;
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
            'statuses' => ApplicationStatus::cases(),
            'branches' => Branch::query()->count(),
            'sellers' => User::query()->whereHas('role', fn ($query) => $query->where('slug', 'vendedor'))->count(),
            'motorcycles' => Motorcycle::query()->where('is_active', true)->count(),
            'recent' => CreditApplication::query()->with(['client', 'motorcycle', 'branch'])->latest()->limit(6)->get(),
        ]);
    }
}
