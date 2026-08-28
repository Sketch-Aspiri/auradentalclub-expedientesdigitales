<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesConsentData;
use Illuminate\Foundation\Http\FormRequest;

class UpdateConsentRequest extends FormRequest
{
    use ValidatesConsentData;

    /**
     * La policy `update` exige además que el consentimiento esté en borrador — un firmado o
     * anulado es inmutable (se corrige anulándolo y creando uno nuevo).
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('consent'));
    }

    /**
     * Un doctor no reasigna el consentimiento al editarlo: se conserva el doctor original.
     * Se fuerza con merge() (no basta remove(): validated() valida sobre all(), que fusiona
     * body + query string).
     */
    protected function prepareForValidation(): void
    {
        if ($this->user()->isDoctor()) {
            $this->merge(['doctor_id' => $this->route('consent')->doctor_id]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'doctor_id' => ['sometimes', 'required', ...$this->doctorIdRule()],
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
