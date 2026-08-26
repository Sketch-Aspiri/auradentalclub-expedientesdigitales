# Reglas de testing

> Fuente: sección 9 de `CLAUDE.md` (raíz del proyecto), complementada con estándares profesionales de Laravel y MySQL. Ante cualquier ambigüedad, `CLAUDE.md` es la referencia autoritativa; ante dudas de cumplimiento normativo de datos clínicos, ver sección 5 de ese archivo.

## Framework

- **Pest** como framework de testing (sintaxis `test()`/`it()` sobre PHPUnit). Si una prueba requiere assertions o setup muy elaborado, PHPUnit clásico (`extends TestCase`) es aceptable, pero Pest es el default.
- Un archivo de prueba por clase/feature bajo revisión: `tests/Unit/...`, `tests/Feature/...`.
- Nombres de archivo en `PascalCase` + sufijo `Test`: `PatientPolicyTest.php`, `StoreConsultationRequestTest.php`.

## Cobertura mínima: 80%

Corre `php artisan test --coverage --min=80` (requiere Xdebug o PCOV) antes de dar una feature por terminada. Prioriza cobertura en:

1. **Form Requests** (validación de datos de entrada).
2. **Policies** (autorización por rol: `doctor`, `administrador`, `superadmin`).
3. **Flujos críticos de negocio**: crear/editar expediente, subir archivo clínico, registrar consentimiento, actualizar odontograma, registrar hoja de evolución con costos.

No optimices cobertura con pruebas triviales (getters, casts automáticos) solo para subir el número — cubre comportamiento, no líneas.

## Tipos de prueba (los tres son obligatorios)

### 1. Unit tests (`tests/Unit`)
- Lógica aislada: cálculos (ej. saldo en `treatment_records`, edad a partir de `birth_date`), value objects, servicios/actions sin tocar base de datos real cuando sea posible.
- No deben requerir framework boot completo salvo que sea estrictamente necesario.

### 2. Feature / Integration tests (`tests/Feature`)
- Rutas HTTP, componentes Livewire (`Livewire::test(...)`), Form Requests, Policies, operaciones de base de datos vía Eloquent.
- Cada endpoint o acción que toque datos de pacientes necesita al menos: caso de éxito, caso de validación fallida, caso de autorización denegada (rol sin permiso).

### 3. E2E / flujos críticos
- Flujos de punta a punta que cruzan varias pantallas/pasos: alta de paciente → historia clínica → consulta → odontograma → consentimiento → carga de archivo.
- Si no hay herramienta E2E de navegador configurada, cubre el flujo completo como Feature test orquestando los pasos en secuencia dentro de la misma prueba.

## Test-Driven Development (obligatorio para features nuevas)

1. Escribe la prueba primero (RED) — debe fallar antes de implementar.
2. Implementación mínima para pasar (GREEN).
3. Refactoriza manteniendo las pruebas en verde (IMPROVE).
4. Verifica cobertura ≥ 80% del código tocado.
5. Si una prueba falla, corrige la implementación — no ajustes la prueba para que pase, salvo que la prueba esté mal planteada (y en ese caso, dilo explícitamente).

## Estructura de pruebas (patrón AAA)

```php
test('calcula el saldo pendiente al registrar un pago parcial', function () {
    // Arrange
    $treatmentRecord = TreatmentRecord::factory()->create([
        'cost' => 1500.00,
        'amount_paid' => 500.00,
    ]);

    // Act
    $balance = $treatmentRecord->balance;

    // Assert
    expect($balance)->toBe(1000.00);
});
```

### Nomenclatura de pruebas

Usa nombres descriptivos que expliquen el comportamiento, no la implementación:

```php
test('deniega el acceso al expediente si el usuario es doctor y no está asignado al paciente', function () {})
test('rechaza la consulta cuando el diagnóstico clínico está vacío', function () {})
test('encripta el campo de alergias antes de persistirlo', function () {})
```

Evita nombres como `test_it_works` o `testPatient1`.

## Factories y seeders (datos realistas mexicanos)

- Usa **factories** de Laravel para generar datos de prueba; usa **seeders** solo para datos de referencia/demo (catálogos de estados del odontograma, usuarios de prueba por rol).
- Los datos deben verse como los de la clínica real: nombres completos mexicanos, formatos de teléfono locales (`+52 55 XXXX XXXX` o similar), direcciones con estados/municipios reales. **No dejes datos genéricos tipo "John Doe" o "Test User" sin avisar explícitamente** que son placeholders temporales.
- Si necesitas CURP/RFC de prueba, genera valores con formato válido pero claramente ficticios (no uses datos reales de personas).
- Ejemplo de factory con Faker localizado:

```php
// PatientFactory.php — usa el locale es_MX del Faker configurado en config/app.php
public function definition(): array
{
    return [
        'full_name' => fake()->name(),
        'birth_date' => fake()->dateTimeBetween('-80 years', '-5 years'),
        'sex' => fake()->randomElement(['M', 'F']),
        'phone' => fake()->numerify('55########'),
        'address' => fake()->address(),
    ];
}
```

## Base de datos de pruebas (MySQL)

- **No uses SQLite en memoria para las pruebas** si el proyecto usa características específicas de MySQL (enums nativos, `JSON`, collations, índices FULLTEXT) — el comportamiento puede diferir y ocultar bugs reales. Configura una base de datos MySQL de pruebas dedicada (`.env.testing` con `DB_DATABASE=aura_dental_testing`).
- Usa el trait `RefreshDatabase` (o `DatabaseTransactions` si el volumen de migraciones lo justifica) en cada test que toque la base de datos, para garantizar aislamiento entre pruebas.
- Nunca corras la suite de pruebas contra la base de datos de desarrollo o producción.
- Verifica que las migraciones corran limpio en la base de pruebas antes de confiar en los resultados (`php artisan migrate:fresh --env=testing` como paso de CI).

### Buenas prácticas de queries en pruebas

- Si una prueba depende de relaciones (`Patient hasMany Consultations`), usa `belongsTo`/`hasMany` factories encadenadas en vez de crear registros sueltos y enlazarlos a mano.
- Para pruebas de autorización, cubre explícitamente cada rol (`doctor`, `administrador`, `superadmin`) contra cada acción protegida — no asumas que probar un rol cubre a los demás.
- Para campos encriptados (`encrypted` cast en Eloquent — alergias, antecedentes médicos, notas clínicas), verifica en la prueba que el valor crudo en la columna de MySQL **no** es texto plano (`assertNotEquals` contra el valor original leyendo la columna directo de la BD), además de verificar que el accessor de Eloquent lo desencripta correctamente.
- Para `audit_logs`, cada prueba de una acción auditable (ver/crear/editar/eliminar sobre datos de paciente) debe verificar que se creó el registro de auditoría correspondiente con el `user_id` y `action` correctos.

## Mocking

- Mockea solo dependencias externas reales (servicios de terceros, filesystem si aplica, envío de correo/notificaciones) — no mockees el ORM ni la base de datos para pruebas de integración.
- Para pruebas de subida de archivos, usa `Storage::fake('local')` (o el disco configurado) en vez de escribir archivos reales en disco.
- No mockees Policies ni Form Requests para "hacer pasar" una prueba de autorización/validación — esas pruebas pierden su propósito si se mockea justo lo que deben verificar.

## Antes de dar una feature por terminada

- [ ] Pruebas unitarias, de integración y de flujo crítico existen y pasan.
- [ ] Cobertura ≥ 80% en el código tocado.
- [ ] Form Requests y Policies nuevas o modificadas tienen prueba explícita.
- [ ] Datos de prueba usan factories con datos mexicanos realistas (o aviso explícito si son placeholders).
- [ ] La suite corre limpio contra la base de datos MySQL de pruebas, no SQLite ni la base de desarrollo.
- [ ] Si el cambio toca datos de pacientes, existe prueba de que se generó el `audit_log` correspondiente.
