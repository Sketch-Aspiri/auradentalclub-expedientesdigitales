<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Edición del propio perfil (nombre y correo). El rol NUNCA se edita aquí — eso vive en la
 * gestión de usuarios (CLAUDE.md §3). Cambiar el correo (vector de recuperación de cuenta)
 * exige confirmar la contraseña actual.
 */
class UpdateProfileRequest extends FormRequest
{
    // Bolsa de errores propia: la vista de perfil tiene dos formularios y ambos usan
    // el campo `current_password`.
    protected $errorBag = 'updateProfile';

    public function authorize(): bool
    {
        // Solo se edita la cuenta autenticada; no hay id de usuario en la ruta.
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        // Normaliza el checkbox a "1"/ausente para que `prohibited_if:remove_photo,1` y el
        // `$request->boolean('remove_photo')` del controlador lean exactamente lo mismo.
        $this->merge([
            'remove_photo' => $this->boolean('remove_photo') ? '1' : '0',
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($this->user()->id),
            ],
            // Foto de perfil: se vuelve a codificar a JPEG al guardarla (App\Support\UserAvatar),
            // lo que descarta EXIF y neutraliza políglotas.
            'photo' => [
                'nullable', 'prohibited_if:remove_photo,1', 'image',
                'mimetypes:image/jpeg,image/png,image/webp', 'max:4096',
                'dimensions:max_width=2500,max_height=2500',
            ],
            'remove_photo' => ['sometimes', 'boolean'],
        ];

        // Solo se pide la contraseña actual si el correo realmente cambia. Si no, el campo
        // no tiene ninguna regla y lo que venga en el formulario (vacío o no) se ignora.
        if ($this->emailIsChanging()) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        return $rules;
    }

    protected function emailIsChanging(): bool
    {
        return $this->filled('email')
            && Str::lower((string) $this->input('email')) !== Str::lower((string) $this->user()->email);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'email.unique' => 'Ese correo electrónico ya está en uso.',
            'current_password.required' => 'Confirma tu contraseña actual para cambiar el correo.',
            'current_password.current_password' => 'La contraseña actual no es correcta.',
            'photo.image' => 'La foto debe ser una imagen.',
            'photo.mimetypes' => 'La foto debe estar en formato JPG, PNG o WebP.',
            'photo.max' => 'La foto no debe pesar más de 4 MB.',
            'photo.dimensions' => 'La foto no debe superar los 2500 × 2500 píxeles.',
            'photo.prohibited_if' => 'No puedes subir una foto y quitar la actual a la vez.',
        ];
    }
}
