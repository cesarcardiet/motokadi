<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Settings\ExchangeRate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExchangeRateController extends Controller
{
    public function edit(ExchangeRate $rates): View
    {
        return view('admin.settings.exchange-rate', [
            'rate' => $rates->current(),
        ]);
    }

    public function update(Request $request, ExchangeRate $rates): RedirectResponse
    {
        $data = $request->validate([
            'usd_ves_rate' => ['required', 'numeric', 'min:0.01', 'max:9999999'],
        ], [
            'usd_ves_rate.required' => 'Indica cuántos bolívares equivalen a 1 dólar.',
            'usd_ves_rate.numeric' => 'La tasa debe ser un número. Ejemplo: 1000',
            'usd_ves_rate.min' => 'La tasa debe ser mayor que cero. Ejemplo: 1000',
        ]);

        $saved = $rates->set((float) $data['usd_ves_rate']);

        return redirect()
            ->route('admin.exchange-rate.edit')
            ->with('status', 'Tasa guardada: '.\App\Support\Money::plain($saved).' bolívares equivalen a 1 dólar.');
    }
}
