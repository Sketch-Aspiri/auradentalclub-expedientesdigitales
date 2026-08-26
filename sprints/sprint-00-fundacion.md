# Sprint 0 — Fundación del proyecto

**Estado:** No iniciado
**Depende de:** —

## Objetivo

Dejar un proyecto Laravel 11 limpio, corriendo, con autenticación propia (sin Breeze/Jetstream), los tres roles, la identidad visual base y el andamiaje de testing/seguridad listos — antes de tocar cualquier módulo clínico.

## Alcance (CLAUDE.md §2, §3, §7, §8)

- Instalación de Laravel 11 + PHP 8.3.
- Base de datos MySQL configurada (local de desarrollo).
- Autenticación construida a medida (login, logout, recuperación de contraseña) — sin starter kit.
- Los tres roles (`superadmin`, `administrador`, `doctor`) modelados en `users.role` con al menos un seeder de cada uno.
- Tailwind CSS configurado con la paleta de marca (`aura-cream`, `aura-gray-light`, `aura-olive`, `aura-sage`, `aura-gray`, `aura-gray-dark`) en `tailwind.config.js`.
- Layout base Blade (navegación, sidebar/topbar según rol) con la identidad minimalista de §7 — sin implementar módulos clínicos todavía.
- Repositorio Git inicializado (privado en GitHub) — `git init` si aún no existe.
- Pest o PHPUnit configurado y corriendo (`.claude/rules/testing.md`).
- Tabla y mecanismo de `audit_logs` (modelo, migración, y un helper/trait/observer base) — aunque todavía no haya nada que auditar, la infraestructura debe existir antes del Sprint 1.

## Tareas

- [ ] `composer create-project laravel/laravel .` (o equivalente) y confirmar PHP 8.3.
- [ ] Configurar `.env` de desarrollo (MySQL, `APP_NAME`, `APP_URL` local).
- [ ] `git init`, primer commit, `.gitignore` correcto (`.env`, `vendor/`, `node_modules/`, `storage/*.key`).
- [ ] Migración `users` con columna `role` (enum `doctor`/`administrador`/`superadmin`).
- [ ] Sistema de auth a medida: login, logout, "olvidé mi contraseña" — usando los defaults de seguridad de Laravel (hashing, throttling, CSRF) sin debilitarlos (`CLAUDE.md` §5).
- [ ] Middleware/Gate base de rol (aún genérico, sin policies de dominio todavía — esas llegan por módulo).
- [ ] Tailwind instalado, `tailwind.config.js` con la paleta de marca.
- [ ] Layout Blade base (`resources/views/layouts/app.blade.php`) con navegación condicionada por rol.
- [ ] Migración + modelo `AuditLog` (`audit_logs`, ver `CLAUDE.md` §6) y un mecanismo reutilizable (trait/observer) para registrar `viewed`/`created`/`updated`/`deleted` que los módulos siguientes puedan enchufar.
- [ ] Pest/PHPUnit instalado y corriendo con al menos un test trivial en verde.
- [ ] Seeders: 1 usuario por rol con datos de prueba (nombres mexicanos, ver `.claude/rules/testing.md`).
- [ ] Verificar que `.claude/settings.json` (hook `validate-bash.sh`, permissions) sigue funcionando en este entorno real de desarrollo, no solo en el repo vacío.

## Criterios de aceptación

- [ ] `php artisan serve` levanta el proyecto sin errores.
- [ ] Login funciona para los tres roles y redirige según corresponda (o al menos deja pasar a un dashboard genérico).
- [ ] `php artisan test` corre y pasa.
- [ ] La paleta de marca es visible en el layout (no hex codes sueltos en las vistas, ver `CLAUDE.md` §7).
- [ ] `audit_logs` existe como tabla y hay una forma clara y documentada (en el código) de escribir en ella desde un controlador/modelo.

## Testing requerido

- Test de que el login/logout funciona por rol.
- Test de que un usuario sin rol válido no puede autenticarse (si aplica una validación de ese tipo).
- Ver `.claude/rules/testing.md` para cobertura mínima y estilo AAA.

## Registro de ejecución

| Fecha | Qué se hizo | Comando/agente usado | Resultado |
|---|---|---|---|
| | | | |
