<?php

namespace App\Http\Requests;

use App\Enums\ApplicationChannel;
use App\Enums\DocumentType;
use App\Models\Motorcycle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCreditApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('solicitudes.crear') ?? false;
    }

    public function rules(): array
    {
        $motorcycle = Motorcycle::query()->find($this->input('motorcycle_id'));
        $colors = $motorcycle?->colors ?? [];

        $rules = [
            'motorcycle_id' => ['required', Rule::exists('motorcycles', 'id')->where('is_active', true)],
            'branch_id' => ['required', Rule::exists('branches', 'id')->where('is_active', true)],
            'channel' => ['required', Rule::enum(ApplicationChannel::class)],
            'color' => ['required', 'string', Rule::in($colors)],
            'address' => ['nullable', 'string', 'max:500', 'required_if:channel,en_linea'],
            'city' => ['required', 'string', 'max:80'],
            'whatsapp_phone' => ['required', 'string', 'min:10', 'max:40', 'regex:/^[0-9+\s()-]+$/'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_if:channel,en_linea'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_if:channel,en_linea'],
        ];

        foreach (DocumentType::required() as $type) {
            $rules['documents.'.$type->value] = ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,webp', 'max:5120', 'required_if:channel,en_linea'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'documents.*.required' => 'Cada recaudo es obligatorio.',
            'documents.*.mimes' => 'Los recaudos deben ser JPG, PNG, WEBP o PDF.',
            'documents.*.max' => 'Cada archivo puede pesar hasta 5 MB.',
            'latitude.required' => 'La ubicación GPS es obligatoria.',
            'longitude.required' => 'La ubicación GPS es obligatoria.',
            'address.max' => 'La dirección es demasiado larga. Acórtala a 500 caracteres o menos.',
            'whatsapp_phone.required' => 'El WhatsApp / teléfono es obligatorio.',
            'whatsapp_phone.regex' => 'Usa un número de WhatsApp válido (solo dígitos, +, espacios o guiones).',
        ];
    }
}
