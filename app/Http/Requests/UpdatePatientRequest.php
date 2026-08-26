<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesPatientData;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientRequest extends FormRequest
{
    use ValidatesPatientData;

    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('patient'));
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return $this->patientRules();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->patientMessages();
    }
}
