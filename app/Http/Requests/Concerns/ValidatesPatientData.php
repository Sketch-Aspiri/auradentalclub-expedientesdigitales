<?php

namespace App\Http\Requests\Concerns;

/**
 * Reglas y mensajes compartidos entre StorePatientRequest y UpdatePatientRequest —
 * evita que ambos Form Requests se desincronicen silenciosamente.
 */
trait ValidatesPatientData
{
    /**
     * @return array<string, array<int, mixed>>
     */
    protected function patientRules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date', 'before_or_equal:today'],
            'sex' => ['required', 'in:M,F'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'marital_status' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^[\d\s+\-()]{7,20}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20', 'regex:/^[\d\s+\-()]{7,20}$/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function patientMessages(): array
    {
        return [
            'full_name.required' => 'El nombre completo es obligatorio.',
            'birth_date.required' => 'La fecha de nacimiento es obligatoria.',
            'birth_date.date' => 'La fecha de nacimiento no es válida.',
            'birth_date.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
            'sex.required' => 'El sexo es obligatorio.',
            'sex.in' => 'El sexo debe ser M o F.',
            'phone.required' => 'El teléfono es obligatorio.',
            'phone.regex' => 'Ingresa un teléfono válido.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'emergency_contact_phone.regex' => 'Ingresa un teléfono de emergencia válido.',
        ];
    }
}
