# 08_ACTION_SYSTEM.md

# VoltStack Framework

## Action System

---

# 1. Overview

El Action System de VoltStack es la capa oficial de lógica de negocio del framework.

Las Actions representan:

* casos de uso,
* operaciones de dominio,
* workflows,
* procesos de negocio,
* y coordinación de servicios.

El objetivo es separar completamente:

* transporte,
* rendering,
* persistencia,
* y lógica de negocio.

---

# 2. Core Philosophy

El sistema está basado en principios:

* business-first architecture,
* transport-independent logic,
* reusable workflows,
* concurrent-ready execution,
* distributed-compatible orchestration,
* runtime-aware operations.

---

# 3. Main Goals

---

## 3.1 Controllers Are Transport

Los Controllers NO contienen lógica compleja.

---

## 3.2 Actions Are Business Logic

Toda lógica reusable debe vivir en Actions.

---

## 3.3 Runtime Independence

Las Actions deben poder ejecutarse desde:

| Runtime             | Supported |
| ------------------- | --------- |
| HTTP                | yes       |
| SPA                 | yes       |
| Live Runtime        | yes       |
| Console             | yes       |
| Queue Workers       | yes       |
| Distributed Workers | yes       |

---

## 3.4 Concurrent Ready

Las Actions deben poder ejecutarse:

* concurrentemente,
* asíncronamente,
* distribuidamente.

---

# 4. Architectural Role

```txt id="w1z7qf"
Controller
    ↓
Action
    ↓
Domain Logic
    ↓
Result
```

---

# 5. Action Responsibilities

Las Actions deben:

* encapsular casos de uso,
* coordinar workflows,
* manejar transacciones,
* orquestar servicios,
* retornar resultados serializables.

---

# 5.1 Forbidden Responsibilities

Las Actions NO deben:

* renderizar vistas,
* acceder directamente al frontend,
* contener lógica HTTP,
* depender del renderer,
* depender del runtime SPA.

---

# 6. Basic Action Structure

---

# 6.1 Example

```php id="d9x4tr"
final class CreateUserAction
{
    public function handle(array $data): User
    {
        //
    }
}
```

---

# 6.2 Action Goals

Las Actions deben ser:

* pequeñas,
* reutilizables,
* testeables,
* serializables,
* composables.

---

# 7. Action Lifecycle

---

# 7.1 Lifecycle Flow

```txt id="n3w7yk"
Action Resolution
    ↓
Dependency Injection
    ↓
Validation
    ↓
Execution
    ↓
Events
    ↓
Serialization
    ↓
Result
```

---

# 7.2 Lifecycle Hooks

| Hook      | Purpose            |
| --------- | ------------------ |
| boot      | initialization     |
| authorize | permissions        |
| validate  | validation         |
| before    | pre execution      |
| handle    | execution          |
| after     | post execution     |
| failed    | exception handling |

---

# 8. Action Resolution

Las Actions son resueltas por el Container.

---

# 8.1 Example

```php id="y7m1qv"
$action = $container->make(
    CreateUserAction::class
);
```

---

# 8.2 Dependency Injection

Las Actions soportan:

* constructor injection,
* contextual bindings,
* lazy services,
* runtime dependencies.

---

# 9. Validation Integration

Las Actions pueden integrar validación.

---

# 9.1 Validation Example

```php id="p8v3xe"
final class CreateUserAction
{
    public function rules(): array
    {
        return [
            'email' => ['required'],
        ];
    }
}
```

---

# 9.2 Validation Goals

* centralized validation,
* reusable validation,
* transport-independent validation.

---

# 10. Authorization Integration

Las Actions pueden definir autorización.

---

# 10.1 Authorization Example

```php id="x5t9an"
public function authorize(): bool
{
    return auth()->check();
}
```

---

# 10.2 Authorization Goals

* runtime permissions,
* policy integration,
* distributed auth compatibility.

---

# 11. Action Results

Las Actions retornan resultados runtime-aware.

---

# 11.1 Result Types

| Type        | Description          |
| ----------- | -------------------- |
| DTO         | structured data      |
| Entity      | domain entity        |
| Collection  | multiple entities    |
| Stream      | streaming            |
| AsyncResult | concurrent execution |

---

# 11.2 Result Example

```php id="f4k2pw"
return new UserData(
    id: 1,
    name: 'Francisco'
);
```

---

# 12. Action Pipelines

Las Actions pueden componerse.

---

# 12.1 Pipeline Flow

```txt id="s9m6vx"
Action A
    ↓
Action B
    ↓
Action C
```

---

# 12.2 Pipeline Example

```php id="r7x4ty"
Pipeline::make()
    ->through([
        ValidateUserAction::class,
        CreateUserAction::class,
        NotifyUserAction::class,
    ]);
```

---

# 13. Nested Actions

Las Actions pueden ejecutar otras Actions.

---

# 13.1 Nested Example

```php id="q3v8mh"
final class RegisterUserAction
{
    public function handle()
    {
        $user = app(CreateUserAction::class)
            ->handle();

        app(SendWelcomeEmailAction::class)
            ->handle($user);
    }
}
```

---

# 14. Transactional Actions

Las Actions pueden ejecutarse transaccionalmente.

---

# 14.1 Transaction Flow

```txt id="j8m2rk"
Transaction Start
    ↓
Action Execution
    ↓
Commit / Rollback
```

---

# 14.2 Transaction Example

```php id="c6v5tn"
Transactional::run(
    fn () => $action->handle()
);
```

---

# 15. Concurrent Actions

VoltStack soporta Actions concurrentes.

---

# 15.1 Concurrent Flow

```txt id="u4n9zp"
Action Dispatch
    ↓
Concurrent Scheduler
    ↓
Parallel Execution
    ↓
Result Aggregation
```

---

# 15.2 Concurrent Example

```php id="v1q8hy"
Concurrent::run([
    fn () => app(LoadUsersAction::class)->handle(),
    fn () => app(LoadOrdersAction::class)->handle(),
]);
```

---

# 16. Async Actions

Las Actions pueden ejecutarse asincrónicamente.

---

# 16.1 Async Example

```php id="m7x1kt"
Dispatch::async(
    CreateReportAction::class
);
```

---

# 16.2 Async Goals

* queue execution,
* distributed workers,
* retries,
* delayed execution.

---

# 17. Distributed Actions

VoltStack prepara Actions distribuidas.

---

# 17.1 Distributed Goals

* remote execution,
* cluster orchestration,
* distributed workflows,
* edge execution.

---

# 17.2 Distributed Flow

```txt id="h2v4qs"
Action Dispatch
    ↓
Distributed Queue
    ↓
Remote Worker
    ↓
Result Sync
```

---

# 18. Action Serialization

Las Actions deben soportar serialización.

---

# 18.1 Serialization Goals

* queue compatibility,
* distributed execution,
* resumability,
* persistence.

---

# 18.2 Serialization Example

```php id="k5w9mn"
SerializedAction::from(
    CreateInvoiceAction::class
);
```

---

# 19. Action Events

Las Actions pueden emitir eventos.

---

# 19.1 Event Example

```php id="p2t7rx"
ActionEvent::dispatch(
    'user.created',
    $payload
);
```

---

# 19.2 Event Categories

| Type               | Description |
| ------------------ | ----------- |
| Lifecycle Events   | execution   |
| Domain Events      | business    |
| Runtime Events     | system      |
| Distributed Events | cluster     |

---

# 20. Action Middleware

Las Actions soportan middleware.

---

# 20.1 Middleware Flow

```txt id="z8m5vp"
Middleware
    ↓
Action
    ↓
Middleware
```

---

# 20.2 Middleware Example

```php id="g3q7xt"
public function middleware(): array
{
    return [
        ThrottleMiddleware::class,
    ];
}
```

---

# 21. Action Contracts

VoltStack soporta contracts formales.

---

# 21.1 Action Contract Example

```php id="l9t4mw"
interface ActionInterface
{
    public function handle(mixed $payload): mixed;
}
```

---

# 21.2 Async Contract

```php id="b1x8kr"
interface AsyncActionInterface
{
    public function queue(): string;
}
```

---

# 22. Action Registry

Las Actions pueden registrarse.

---

# 22.1 Registry Example

```php id="q7m2vz"
ActionRegistry::register(
    'create-user',
    CreateUserAction::class
);
```

---

# 22.2 Registry Goals

* runtime discovery,
* action metadata,
* distributed lookup,
* orchestration.

---

# 23. Action Monitoring

VoltStack soportará observabilidad.

---

# 23.1 Monitoring Features

* tracing
* metrics
* execution timing
* retries
* distributed tracing

---

# 23.2 Monitoring Flow

```txt id="t8x3qn"
Action Execution
    ↓
Telemetry Runtime
    ↓
Metrics Aggregation
```

---

# 24. Security Model

El Action System debe soportar:

* signed payloads,
* runtime authorization,
* permission validation,
* distributed security,
* replay protection.

---

# 25. Action Extension System

Las Actions son extensibles.

---

# 25.1 Extension Types

* middleware
* lifecycle hooks
* runtime plugins
* distributed orchestration
* telemetry extensions

---

# 26. Long-Term Vision

VoltStack busca que las Actions evolucionen hacia:

* distributed workflows,
* reactive orchestration,
* cloud-native execution,
* concurrent business pipelines,
* runtime-driven enterprise systems.

---

# 27. Action System Motto

> “Controllers transport. Actions execute business.”
