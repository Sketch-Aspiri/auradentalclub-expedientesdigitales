<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Política de contraseñas para todo el proyecto (restablecimiento y cambio de
        // contraseña en el perfil). Sistema con datos clínicos (NOM-004): mínimo 12.
        Password::defaults(fn () => Password::min(12));
    }
}
