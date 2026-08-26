# Convenciones de código

> Fuente: sección 8 de `CLAUDE.md` (raíz del proyecto). Ante cualquier ambigüedad, ese archivo es la referencia autoritativa.

## Idioma

- **Código en inglés:** nombres de tablas, columnas, variables, clases, métodos.
- **Interfaz en español:** todos los textos visibles al usuario (labels, mensajes, validaciones mostradas en pantalla).

## Estilo

- Sigue el estándar **Laravel / PSR-12**.
- `snake_case` en base de datos (tablas y columnas).
- `PascalCase` en clases.
- `camelCase` en métodos y variables.

## Convenciones idiomáticas de Laravel

- **Eloquent** para el ORM — evita queries SQL crudas salvo necesidad justificada.
- **Form Requests** para validación de entrada (no valides "a mano" dentro del controlador/componente).
- **Policies** para autorización — cada ruta/acción que toque datos de pacientes necesita una policy explícita; nunca asumas que "ya está protegido".
- **Resource Controllers** cuando aplique, para mantener las acciones CRUD consistentes.

## Migraciones

- Descriptivas y reversibles (implementa `up()` y `down()` correctamente).
- **Nunca** modifiques una migración que ya "corrió" en producción — crea una nueva migración para el cambio.

## Componentes Livewire

- Responsabilidad única por componente.
- Evita lógica de negocio pesada dentro del componente; muévela a **servicios/actions** cuando crezca en complejidad.

## Notas de contexto del proyecto

- Este sistema maneja datos clínicos sensibles (ver sección 5 de `CLAUDE.md`): al nombrar/diseñar campos, respeta la estructura del expediente clínico conforme a NOM-004-SSA3-2012, NOM-015-SSA2-2015 y NOM-013-SSA2-2015.
- No introduzcas capacidades de roles no definidas en `CLAUDE.md` (sección 3) sin confirmar antes con el usuario.
