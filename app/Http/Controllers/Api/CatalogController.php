<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\MotorcycleResource;
use App\Models\Branch;
use App\Models\Motorcycle;
use App\Support\Money;
use Illuminate\Http\JsonResponse;

class CatalogController extends Controller
{
    public function motorcycles(): JsonResponse
    {
        $items = Motorcycle::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => MotorcycleResource::collection($items),
            'exchange_rate' => Money::rate(),
        ]);
    }

    public function motorcycle(Motorcycle $motorcycle): MotorcycleResource
    {
        abort_unless($motorcycle->is_active, 404);

        return new MotorcycleResource($motorcycle);
    }

    public function branches(): JsonResponse
    {
        $branches = Branch::query()
            ->where('is_active', true)
            ->orderBy('city')
            ->orderBy('name')
            ->get(['id', 'name', 'city', 'address', 'phone']);

        return response()->json(['data' => $branches]);
    }
}
