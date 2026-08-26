<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesPatientData;
use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
{
    use ValidatesPatientData;

    public function authorize(): bool
    {
        return $this->user()->can('create', Patient::class);
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
