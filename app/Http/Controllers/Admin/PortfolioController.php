<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InstallmentStatus;
use App\Http\Controllers\Controller;
use App\Models\FinancingContract;
use App\Models\Installment;
use App\Models\User;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    public function contracts(): View
    {
        $contracts = FinancingContract::query()
            ->with(['client', 'motorcycle', 'branch', 'installments'])
            ->latest('signed_at')
            ->paginate(12);

        $lateCount = Installment::query()->where('status', InstallmentStatus::Late)->count();

        return view('admin.portfolio.contracts', compact('contracts', 'lateCount'));
    }

    public function show(FinancingContract $contract): View
    {
        $contract->load(['client', 'motorcycle', 'branch', 'application', 'installments']);

        return view('admin.portfolio.show', compact('contract'));
    }

    public function profiles(): View
    {
        $profiles = User::query()
            ->where('is_reference_profile', true)
            ->with(['contracts.installments', 'contracts.motorcycle'])
            ->orderBy('name')
            ->get();

        return view('admin.portfolio.profiles', compact('profiles'));
    }
}
