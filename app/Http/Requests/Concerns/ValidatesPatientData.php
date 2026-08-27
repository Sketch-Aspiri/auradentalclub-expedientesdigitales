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
            // Foto de identificación: opcional, imagen real (mimetype, no solo extensión), 4 MB.
            // La imagen se vuelve a codificar a JPEG al guardarla (App\Support\PatientPhoto),
            // lo que descarta EXIF y neutraliza políglotas; el tope de dimensiones acota el
            // trabajo de ese decodificado en el servidor.
            'photo' => ['nullable', 'prohibited_if:remove_photo,1', 'image', 'mimetypes:image/jpeg,image/png,image/webp', 'max:4096', 'dimensions:max_width=2500,max_height=2500'],
            'remove_photo' => ['sometimes', 'boolean'],
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
            'photo.image' => 'La foto debe ser una imagen.',
            'photo.mimetypes' => 'La foto debe estar en formato JPG, PNG o WebP.',
            'photo.max' => 'La foto no debe pesar más de 4 MB.',
            'photo.dimensions' => 'La foto no debe superar los 2500 × 2500 píxeles.',
            'photo.prohibited_if' => 'No puedes subir una foto y quitar la actual a la vez.',
        ];
    }
}
