<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMotorcycleRequest;
use App\Models\Motorcycle;
use App\Services\Financing\InstallmentSimulator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MotorcycleController extends Controller
{
    public function index(): View
    {
        return view('admin.motorcycles.index', [
            'motorcycles' => Motorcycle::query()->orderBy('sort_order')->paginate(8),
        ]);
    }

    public function create(): View
    {
        return view('admin.motorcycles.form', [
            'motorcycle' => new Motorcycle([
                'line' => 'sincronica',
                'term_months' => InstallmentSimulator::MONTHS,
                'price_source' => 'estimado',
                'is_active' => true,
                'colors' => ['Negro', 'Rojo'],
            ]),
        ]);
    }

    public function store(StoreMotorcycleRequest $request): RedirectResponse
    {
        $next = (int) Motorcycle::query()->max('sort_order') + 10;
        Motorcycle::query()->create($this->payload($request, $next));

        return redirect()->route('admin.motorcycles.index')->with('status', 'Modelo agregado al catálogo.');
    }

    public function edit(Motorcycle $motorcycle): View
    {
        return view('admin.motorcycles.form', compact('motorcycle'));
    }

    public function update(StoreMotorcycleRequest $request, Motorcycle $motorcycle): RedirectResponse
    {
        $motorcycle->update($this->payload($request, $motorcycle->sort_order));

        return redirect()->route('admin.motorcycles.index')->with('status', 'Precios del modelo actualizados. Las solicitudes ya abiertas conservan su plan.');
    }

    /**
     * @return array<string, mixed>
     */
    private function priceSource(StoreMotorcycleRequest $request): string
    {
        $existing = $request->route('motorcycle');

        if (! $existing instanceof Motorcycle) {
            return 'estimado';
        }

        $samePrice = number_format((float) $existing->price, 2, '.', '') === number_format((float) $request->input('price'), 2, '.', '');
        $sameInitial = number_format((float) $existing->initial_payment, 2, '.', '') === number_format((float) $request->input('initial_payment'), 2, '.', '');

        return $samePrice && $sameInitial ? $existing->price_source : 'estimado';
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(StoreMotorcycleRequest $request, int $sortOrder): array
    {
        return [
            'name' => $request->string('name')->toString(),
            'slug' => Str::slug($request->string('slug')->toString()),
            'line' => $request->string('line')->toString(),
            'displacement' => $request->integer('displacement'),
            'summary' => $request->string('summary')->toString(),
            'price' => $request->input('price'),
            'initial_payment' => $request->input('initial_payment'),
            'term_months' => $request->integer('term_months'),
            'price_source' => $this->priceSource($request),
            'colors' => $request->colors(),
            'sort_order' => $sortOrder,
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
