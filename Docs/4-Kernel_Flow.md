# 04_KERNEL_FLOW.md

# VoltStack Framework

## Kernel Flow

---

# 1. Overview

El Kernel de VoltStack es el núcleo operativo responsable de coordinar el ciclo completo de ejecución de una request dentro del framework.

El Kernel NO contiene lógica de negocio.

Su responsabilidad es:

* recibir requests,
* inicializar runtimes,
* ejecutar pipelines,
* resolver rutas,
* despachar acciones,
* ejecutar rendering,
* y retornar responses.

---

# 2. Kernel Philosophy

El Kernel está diseñado bajo principios:

* lightweight runtime,
* middleware-driven architecture,
* pipeline execution,
* contract-based orchestration,
* replaceable runtime layers,
* concurrent-ready execution.

---

# 3. Main Responsibilities

El Kernel es responsable de:

| Responsibility       | Description              |
| -------------------- | ------------------------ |
| Request Handling     | Procesar requests        |
| Middleware Execution | Ejecutar pipelines       |
| Route Resolution     | Resolver rutas           |
| Controller Dispatch  | Ejecutar transport layer |
| Action Execution     | Ejecutar business layer  |
| Response Rendering   | Generar response         |
| Exception Management | Manejar errores          |
| Runtime Coordination | Coordinar runtimes       |

---

# 4. Global Request Lifecycle

```txt id="g5b1s7"
Incoming Request
    ↓
Bootstrap Runtime
    ↓
HTTP Kernel
    ↓
Global Middleware Pipeline
    ↓
Router Runtime
    ↓
Route Middleware Pipeline
    ↓
Controller Dispatcher
    ↓
Action Runtime
    ↓
Response Factory
    ↓
Renderer Runtime
    ↓
Outgoing Response
```

---

# 5. Bootstrap Stage

Antes del Kernel, VoltStack ejecuta el Bootstrap Runtime.

---

# 5.1 Bootstrap Flow

```txt id="2ubx9j"
Environment Loading
    ↓
Configuration Loading
    ↓
Container Initialization
    ↓
Provider Registration
    ↓
Runtime Registration
    ↓
Kernel Boot
```

---

# 5.2 Bootstrap Responsibilities

El bootstrap debe:

* cargar configuración,
* inicializar el container,
* registrar providers,
* registrar runtimes,
* preparar el framework.

---

# 6. HTTP Kernel Architecture

Clase conceptual:

```php id="h5j2r2"
interface HttpKernelInterface
{
    public function handle(Request $request): Response;
}
```

---

# 6.1 Kernel Responsibilities

El Kernel debe:

* recibir requests,
* construir pipelines,
* coordinar runtimes,
* manejar excepciones,
* retornar responses.

---

# 6.2 Kernel Independence

El Kernel NO debe depender directamente de:

```txt id="nt4mfx"
database
orm
auth
views
storage
```

Todo debe resolverse mediante:

* contracts,
* providers,
* middleware,
* adapters.

---

# 7. Request Object Lifecycle

La request pasa por múltiples etapas.

---

# 7.1 Request Creation

```txt id="6u8n7u"
PHP Globals
    ↓
Request Factory
    ↓
Immutable Request Object
```

---

# 7.2 Request Responsibilities

La Request debe encapsular:

* headers
* cookies
* uploaded files
* query params
* body
* streams
* metadata

---

# 7.3 Immutable Design

Las requests deben ser inmutables.

Ejemplo:

```php id="8xhgjg"
$request = $request->withAttribute('tenant', $tenant);
```

---

# 8. Middleware Pipeline

VoltStack utiliza pipelines desacoplados.

---

# 8.1 Middleware Flow

```txt id="qg6qre"
Request
    ↓
Middleware 1
    ↓
Middleware 2
    ↓
Middleware 3
    ↓
Controller Dispatcher
```

---

# 8.2 Middleware Interface

```php id="6jif3l"
interface MiddlewareInterface
{
    public function handle(
        Request $request,
        Closure $next
    ): Response;
}
```

---

# 8.3 Middleware Categories

---

## Global Middleware

Siempre ejecutados.

Ejemplos:

* trusted proxies
* CORS
* request normalization
* security headers

---

## Route Middleware

Aplicados por ruta.

Ejemplos:

* auth
* throttle
* permissions
* tenant resolution

---

## Runtime Middleware

Aplicados por runtime.

Ejemplos:

* SPA hydration
* Live runtime sync
* websocket state sync

---

# 9. Router Runtime

El Router es independiente del Kernel.

---

# 9.1 Router Responsibilities

* route registration
* URI matching
* parameter extraction
* middleware resolution
* route dispatch preparation

---

# 9.2 Route Resolution Flow

```txt id="g8onzz"
Request URI
    ↓
Route Collection
    ↓
Route Matcher
    ↓
Route Resolver
    ↓
Resolved Route
```

---

# 9.3 Route Output

El Router retorna:

```php id="zvmkzj"
ResolvedRoute
{
    controller,
    action,
    middleware,
    parameters,
    metadata
}
```

---

# 10. Controller Dispatching

Los Controllers representan únicamente transport layer.

---

# 10.1 Dispatch Flow

```txt id="f7hmjr"
Resolved Route
    ↓
Controller Resolver
    ↓
Dependency Injection
    ↓
Method Invocation
```

---

# 10.2 Controller Responsibilities

Los Controllers deben:

* validar transport data,
* coordinar actions,
* retornar responses.

NO deben contener:

* lógica compleja,
* queries pesadas,
* rendering acoplado.

---

# 11. Action Runtime

Las Actions contienen la lógica de negocio.

---

# 11.1 Action Flow

```txt id="xgc7zn"
Controller
    ↓
Action Dispatcher
    ↓
Business Logic
    ↓
Domain Result
```

---

# 11.2 Action Responsibilities

Las Actions deben:

* encapsular casos de uso,
* coordinar procesos,
* ejecutar lógica reusable,
* soportar concurrencia futura.

---

# 11.3 Action Example

```php id="0bjlwm"
final class CreateOrderAction
{
    public function handle(array $data): Order
    {
        //
    }
}
```

---

# 12. Response Factory

VoltStack centraliza la creación de responses.

---

# 12.1 Response Types

| Type            | Description       |
| --------------- | ----------------- |
| HTML Response   | SSR               |
| JSON Response   | APIs              |
| Stream Response | Streaming         |
| SPA Response    | Hydration Payload |
| Live Response   | Reactive Payload  |
| File Response   | Downloads         |

---

# 12.2 Response Flow

```txt id="dqqo7w"
Action Result
    ↓
Response Factory
    ↓
Concrete Response
```

---

# 13. Rendering Runtime

El Renderer Runtime decide cómo representar la response.

---

# 13.1 Renderer Modes

| Renderer        | Purpose   |
| --------------- | --------- |
| View Renderer   | SSR       |
| SPA Renderer    | Hydration |
| Live Renderer   | Realtime  |
| Stream Renderer | Streaming |
| API Renderer    | JSON      |

---

# 13.2 Renderer Flow

```txt id="6ylu4m"
Response Object
    ↓
Renderer Resolver
    ↓
Concrete Renderer
    ↓
Serialized Output
```

---

# 14. SPA Kernel Flow

Cuando se usa:

```txt id="v6qv0z"
quantum/spa
```

el flujo cambia.

---

# 14.1 SPA Request Lifecycle

```txt id="4jy6xn"
Request
    ↓
Kernel
    ↓
Controller
    ↓
Screen Builder
    ↓
Protocol Serializer
    ↓
JSON Runtime Payload
    ↓
Frontend Hydration
```

---

# 14.2 SPA Payload Example

```json id="zqj0c8"
{
  "screen": "Dashboard",
  "state": {},
  "components": [],
  "effects": [],
  "transitions": {}
}
```

---

# 15. Live Runtime Flow

Cuando se usa:

```txt id="dxh3pk"
quantum/live
```

el runtime es persistente.

---

# 15.1 Live Runtime Lifecycle

```txt id="y2h1xk"
Initial Request
    ↓
Component Hydration
    ↓
Persistent Runtime
    ↓
Realtime Events
    ↓
Partial Updates
    ↓
DOM Synchronization
```

---

# 16. Exception Flow

Las excepciones son manejadas centralmente.

---

# 16.1 Exception Pipeline

```txt id="wjlwm3"
Exception Thrown
    ↓
Exception Mapper
    ↓
Exception Renderer
    ↓
Formatted Response
```

---

# 16.2 Exception Renderer Types

| Type                | Description |
| ------------------- | ----------- |
| HTML Error Renderer | Web         |
| JSON Error Renderer | APIs        |
| SPA Error Renderer  | Hydration   |
| Live Error Renderer | Realtime    |

---

# 17. Concurrency Flow

VoltStack está preparado para concurrencia.

---

# 17.1 Concurrent Runtime

```txt id="r7j1nq"
Request
    ↓
Concurrent Dispatcher
    ↓
Parallel Tasks
    ↓
Task Synchronization
    ↓
Unified Response
```

---

# 17.2 Concurrent Example

```php id="lwv33u"
Concurrent::run([
    fn () => $users->load(),
    fn () => $orders->load(),
    fn () => $analytics->load(),
]);
```

---

# 18. Process Runtime Flow

VoltStack soportará procesos persistentes.

---

# 18.1 Worker Runtime

```txt id="ivdql5"
Worker Start
    ↓
Runtime Boot
    ↓
Persistent Container
    ↓
Task Loop
    ↓
Graceful Shutdown
```

---

# 18.2 Supported Runtimes

* FrankenPHP
* RoadRunner
* Swoole
* ReactPHP

---

# 19. Runtime Coordination

VoltStack utiliza un Runtime Manager central.

---

# 19.1 Runtime Responsibilities

El Runtime Manager coordina:

* HTTP runtime
* SPA runtime
* Live runtime
* Queue runtime
* Worker runtime

---

# 19.2 Runtime Registry

Ejemplo conceptual:

```php id="98thv2"
RuntimeRegistry::register(
    'spa',
    SpaRuntime::class
);
```

---

# 20. Extension Flow

VoltStack permite extender el Kernel.

---

# 20.1 Extension Points

* middleware
* providers
* runtime hooks
* event listeners
* renderers
* response transformers

---

# 20.2 Hook Pipeline

```txt id="ivjlwm"
Request
    ↓
Hook Dispatcher
    ↓
Extension Runtime
```

---

# 21. Performance Strategy

El Kernel está diseñado para:

* mínimo boot time,
* lazy loading,
* persistent runtimes,
* low memory usage,
* concurrent execution.

---

# 21.1 Optimization Goals

| Goal               | Description         |
| ------------------ | ------------------- |
| Fast Boot          | Arranque rápido     |
| Minimal Memory     | Bajo consumo        |
| Persistent Runtime | Runtime persistente |
| Streaming Support  | Streaming           |
| Concurrent Tasks   | Paralelismo         |
| Runtime Reuse      | Reutilización       |

---

# 22. Long-Term Kernel Vision

El Kernel de VoltStack evolucionará hacia:

* runtime orchestration,
* distributed execution,
* cloud-native runtimes,
* edge rendering,
* resumable rendering,
* reactive server-driven applications.

---

# 23. Kernel Motto

> “The Kernel coordinates. The runtimes execute. The framework scales.”
