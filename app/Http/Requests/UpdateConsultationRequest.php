<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesConsultationData;
use Illuminate\Foundation\Http\FormRequest;

class UpdateConsultationRequest extends FormRequest
{
    use ValidatesConsultationData;

    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('consultation'));
    }

    /**
     * Un doctor no reasigna la consulta al editarla: el doctor tratante original se conserva.
     * Solo administrador/superadmin pueden cambiar el doctor_id.
     *
     * Se fuerza el valor original con merge() (no basta con remove(): validated() valida
     * sobre all(), que fusiona body + query string, y un doctor podría colar
     * ?doctor_id=... por la URL). Con merge() el body gana sobre el query.
     */
    protected function prepareForValidation(): void
    {
        if ($this->user()->isDoctor()) {
            $this->merge(['doctor_id' => $this->route('consultation')->doctor_id]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'doctor_id' => ['sometimes', 'required', ...$this->doctorIdRule()],
            ...$this->consultationRules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->consultationMessages();
    }
}
