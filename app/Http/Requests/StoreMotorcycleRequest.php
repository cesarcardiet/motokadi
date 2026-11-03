<?php

namespace App\Http\Requests;

use App\Services\Financing\InstallmentSimulator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMotorcycleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('motos.gestionar') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:120', Rule::unique('motorcycles', 'slug')->ignore($this->route('motorcycle'))],
            'line' => ['required', Rule::in(['enduro', 'atv', 'sincronica', 'automatica', 'carguero'])],
            'displacement' => ['required', 'integer', 'min:50', 'max:2000'],
            'summary' => ['required', 'string', 'max:240'],
            'price' => ['required', 'numeric', 'min:1'],
            'initial_payment' => ['required', 'numeric', 'min:0', 'lte:price'],
            'term_months' => ['required', 'integer', 'min:1', 'max:36'],
            'colors' => ['required', 'string', 'max:180'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'initial_payment.lte' => 'La inicial no puede ser mayor que el precio.',
        ];
    }

    /**
     * @return list<string>
     */
    public function colors(): array
    {
        return collect(explode(',', (string) $this->input('colors')))
            ->map(fn (string $color) => trim($color))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function quoteMonths(): int
    {
        return (int) $this->input('term_months', InstallmentSimulator::MONTHS);
    }
}
