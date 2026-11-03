<?php

namespace App\Http\Controllers;

use App\Models\Motorcycle;
use App\Support\Catalog\KadiCatalog;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function home(): View
    {
        $catalog = Motorcycle::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('public.home', [
            'featured' => $catalog,
            'hero' => $catalog->firstWhere('slug', 'furioso-250'),
        ]);
    }

    public function index(): View
    {
        $line = request()->string('linea')->toString();
        $base = Motorcycle::query()->where('is_active', true);
        $counts = [
            '' => (clone $base)->count(),
            'enduro' => (clone $base)->whereIn('line', ['enduro', 'atv'])->count(),
            'sincronica' => (clone $base)->where('line', 'sincronica')->count(),
            'automatica' => (clone $base)->where('line', 'automatica')->count(),
            'carguero' => (clone $base)->where('line', 'carguero')->count(),
        ];

        $query = (clone $base)->orderBy('sort_order');

        if ($line === 'enduro') {
            $query->whereIn('line', ['enduro', 'atv']);
        } elseif ($line !== '') {
            $query->where('line', $line);
        }

        return view('public.catalog', [
            'motorcycles' => $query->paginate(8)->withQueryString(),
            'line' => $line,
            'counts' => $counts,
            'filters' => [
                '' => 'Todas',
                'enduro' => 'Enduro · ATV',
                'sincronica' => 'Sincrónicas',
                'automatica' => 'Automáticas',
                'carguero' => 'Cargueros',
            ],
        ]);
    }

    public function show(Motorcycle $motorcycle): View
    {
        abort_unless($motorcycle->is_active, 404);

        return view('public.motorcycle', [
            'motorcycle' => $motorcycle,
            'quote' => $motorcycle->quote(),
            'lineLabel' => KadiCatalog::lineLabel($motorcycle->line),
        ]);
    }

    public function simulation(): View
    {
        $motorcycles = Motorcycle::query()
            ->where('is_active', true)
            ->orderByRaw("case when price_source = 'ejemplo' then 0 else 1 end")
            ->orderBy('sort_order')
            ->paginate(8);

        return view('public.simulation', [
            'motorcycles' => $motorcycles,
        ]);
    }

    public function requirements(): View
    {
        return view('public.requirements');
    }
}
