<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VoidConsentRequest extends FormRequest
{
    /**
     * La policy `void` exige que el consentimiento esté firmado.
     */
    public function authorize(): bool
    {
        return $this->user()->can('void', $this->route('consent'));
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'void_reason' => ['required', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'void_reason.required' => 'Escribe el motivo de la anulación.',
            'void_reason.max' => 'El motivo de la anulación es demasiado largo.',
        ];
    }
}
