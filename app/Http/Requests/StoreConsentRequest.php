<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesConsentData;
use App\Models\Consent;
use Illuminate\Foundation\Http\FormRequest;

class StoreConsentRequest extends FormRequest
{
    use ValidatesConsentData;

    public function authorize(): bool
    {
        return $this->user()->can('create', Consent::class);
    }

    /**
     * Si quien registra es un doctor, el consentimiento se le asigna y el campo no es
     * seleccionable. administrador/superadmin sí eligen el doctor de una lista.
     */
    protected function prepareForValidation(): void
    {
        if ($this->user()->isDoctor()) {
            $this->merge(['doctor_id' => $this->user()->id]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'doctor_id' => ['required', ...$this->doctorIdRule()],
            ...$this->consentRules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->consentMessages();
    }
}
