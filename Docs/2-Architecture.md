# 02_ARCHITECTURE.md

# VoltStack Framework

## Architecture

---

# 1. Architecture Overview

VoltStack es un framework PHP modular, desacoplado y orientado a runtime, diseñado para soportar desde aplicaciones web simples hasta plataformas distribuidas y sistemas fullstack reactivos.

La arquitectura está basada en:

* capas desacopladas,
* contratos,
* pipelines,
* runtimes,
* adaptadores,
* y paquetes progresivos.

El framework se construye bajo el principio:

> “Instala únicamente lo que realmente necesitas.”

---

# 2. Core Architectural Principles

---

## 2.1 Modular First

Cada subsistema es un paquete independiente.

Ejemplo:

```txt
quantum/routing
```

no debe depender de:

```txt
quantum/database
quantum/auth
quantum/session
```

---

## 2.2 Backend First

El backend define:

* estado,
* protocolos,
* rendering,
* reglas de negocio,
* componentes,
* acciones.

El frontend únicamente hidrata y representa.

---

## 2.3 Runtime Driven

VoltStack funciona mediante runtimes internos.

Ejemplo:

* HTTP Runtime
* SPA Runtime
* Live Runtime
* Console Runtime
* Worker Runtime

---

## 2.4 Contract Based

Los paquetes se comunican mediante:

* interfaces,
* contracts,
* events,
* pipelines,
* adapters.

Nunca mediante dependencias rígidas.

---

## 2.5 Progressive Scaling

Una aplicación puede iniciar como:

```txt
Simple Website
```

y evolucionar hacia:

```txt
Realtime Enterprise Distributed Platform
```

sin migrar de stack.

---

## 2.6 Replaceable Systems

Todos los sistemas deben poder reemplazarse:

* cache
* router
* renderer
* storage
* auth
* SPA runtime
* event dispatcher
* process manager

---

# 3. Global Architecture

```txt
Application
    ↓
Bootstrap Runtime
    ↓
Kernel Runtime
    ↓
Middleware Pipeline
    ↓
Router Runtime
    ↓
Controller / Action Runtime
    ↓
Response Factory
    ↓
Renderer Runtime
    ↓
SPA / View Runtime
    ↓
HTTP Response
```

---

# 4. Architectural Layers

VoltStack divide el sistema en capas desacopladas.

---

# 4.1 Foundation Layer

Responsable de inicializar el framework.

Paquetes:

```txt
quantum/bootstrap
quantum/config
quantum/container
quantum/console
```

Responsabilidades:

* bootstrapping
* environment loading
* service registration
* dependency injection
* command runtime

---

# 4.2 HTTP Layer

Responsable del ciclo HTTP.

Paquetes:

```txt
quantum/http
quantum/http-kernel
quantum/routing
quantum/controllers
```

Responsabilidades:

* requests
* responses
* middleware
* route dispatching
* transport layer

---

# 4.3 Application Layer

Responsable de la lógica de negocio.

Paquetes:

```txt
quantum/actions
quantum/validation
quantum/exceptions
quantum/process
quantum/concurrency
```

Responsabilidades:

* business logic
* validation
* exception handling
* concurrent execution
* process orchestration

---

# 4.4 Presentation Layer

Responsable del rendering y representación visual.

Paquetes:

```txt
quantum/view
quantum/components
quantum/spa
quantum/live
```

Responsabilidades:

* views
* hydration
* SPA rendering
* reactive runtime
* visual contracts
* frontend protocols

---

# 4.5 Infrastructure Layer

Responsable de integración externa.

Paquetes opcionales:

```txt
quantum/database
quantum/storage
quantum/cache
quantum/mail
quantum/queue
quantum/events
```

Responsabilidades:

* persistence
* queues
* storage
* cache
* event transport

---

# 5. Package Dependency Rules

VoltStack impone reglas estrictas de dependencias.

---

## Foundation Rules

```txt
bootstrap
    ↓
config
    ↓
container
```

---

## HTTP Rules

```txt
routing → http
controllers → routing + container
http-kernel → http + routing + container
```

---

## Application Rules

```txt
actions → none
validation → none
exceptions → none
```

---

## Presentation Rules

```txt
view → container
components → view
spa → http + components
live → spa + concurrency
```

---

## Forbidden Dependencies

Ejemplos prohibidos:

```txt
routing → database
validation → session
http → auth
actions → views
```

---

# 6. Bootstrap Lifecycle

VoltStack inicia mediante un runtime de bootstrap.

---

# 6.1 Startup Flow

```txt
public/index.php
    ↓
BootstrapManager
    ↓
EnvironmentLoader
    ↓
ConfigurationLoader
    ↓
ContainerInitialization
    ↓
ProviderRegistration
    ↓
KernelBoot
    ↓
ApplicationReady
```

---

# 6.2 Bootstrap Responsibilities

El bootstrap debe:

* cargar variables de entorno,
* registrar providers,
* construir el container,
* cargar configuración,
* inicializar runtimes.

---

# 7. HTTP Lifecycle

---

# 7.1 Request Lifecycle

```txt
Incoming Request
    ↓
HTTP Kernel
    ↓
Global Middleware
    ↓
Route Resolution
    ↓
Route Middleware
    ↓
Controller / Action
    ↓
Response Factory
    ↓
Renderer Runtime
    ↓
Outgoing Response
```

---

# 7.2 HTTP Kernel Responsibilities

El HTTP Kernel debe:

* manejar requests,
* ejecutar middleware,
* resolver rutas,
* ejecutar controladores,
* retornar responses.

---

# 8. Action System Architecture

VoltStack separa:

* transporte,
* lógica,
* rendering.

---

## 8.1 Controllers

Los controllers son únicamente transport layer.

Ejemplo:

```php
final class UserController
{
    public function store(CreateUserAction $action)
    {
        return $action->handle();
    }
}
```

---

## 8.2 Actions

Las acciones contienen la lógica de negocio.

Ejemplo:

```php
final class CreateUserAction
{
    public function handle(array $data): User
    {
        //
    }
}
```

---

## 8.3 Benefits

Ventajas:

* testabilidad,
* reutilización,
* desacoplamiento,
* arquitectura enterprise,
* soporte concurrente,
* integración futura con workers.

---

# 9. Routing Architecture

VoltStack utiliza un router desacoplado.

---

## 9.1 Router Responsibilities

* route registration
* route matching
* middleware assignment
* route grouping
* parameter binding

---

## 9.2 Router Independence

El router NO debe depender de:

```txt
database
session
auth
views
```

---

# 10. Rendering Architecture

VoltStack soporta múltiples modos de rendering.

---

# 10.1 Rendering Modes

| Mode      | Description           |
| --------- | --------------------- |
| SSR       | Server Side Rendering |
| SPA       | Full Hydration        |
| Islands   | Partial Hydration     |
| Streaming | Chunk Rendering       |
| Live      | Realtime Rendering    |

---

# 10.2 Renderer Runtime

El renderer selecciona dinámicamente:

* Blade-like rendering,
* SPA rendering,
* Live runtime rendering,
* streaming rendering.

---

# 11. SPA Runtime Architecture

El sistema SPA se basa en un protocolo unificado.

---

# 11.1 SPA Flow

```txt
PHP Screen Definition
    ↓
Protocol Serialization
    ↓
JSON Runtime Payload
    ↓
Frontend Hydration
    ↓
Reactive State Runtime
```

---

# 11.2 SPA Runtime Responsibilities

El runtime SPA debe manejar:

* screens,
* components,
* state,
* transitions,
* effects,
* hydration,
* navigation,
* synchronization.

---

# 11.3 Official Adapters

```txt
quantum/spa-react
quantum/spa-vue
quantum/spa-svelte
quantum/spa-solid
```

---

# 12. Live Runtime Architecture

VoltStack incluirá un runtime reactivo propio.

Paquete:

```txt
quantum/live
```

Inspirado en:

* Livewire
* Phoenix LiveView
* Hotwire
* Qwik
* React Server Components

---

# 12.1 Live Runtime Goals

Debe soportar:

* server-driven UI,
* realtime state,
* websocket sync,
* partial updates,
* streaming,
* resumability.

---

# 13. Component Architecture

Los componentes son entidades backend-first.

---

# 13.1 Component Structure

Un componente puede definir:

* state
* props
* events
* actions
* slots
* visual contracts
* rendering strategy

---

# 13.2 Component Runtime

El runtime administra:

* hydration
* state synchronization
* event dispatching
* frontend binding
* rendering lifecycle

---

# 14. Concurrency Architecture

VoltStack tendrá soporte nativo para concurrencia.

Paquete:

```txt
quantum/concurrency
```

---

# 14.1 Concurrency Goals

Soporte para:

* fibers
* async tasks
* coroutine execution
* worker pools
* parallel execution
* distributed jobs

---

# 14.2 Concurrent Runtime

Ejemplo conceptual:

```php
Concurrent::run([
    fn () => $serviceA->execute(),
    fn () => $serviceB->execute(),
]);
```

---

# 15. Process Architecture

Paquete:

```txt
quantum/process
```

Responsable de:

* process spawning
* process pools
* subprocess management
* daemon execution
* worker orchestration

---

# 16. Runtime Modes

VoltStack soportará múltiples runtimes.

| Runtime         | Purpose              |
| --------------- | -------------------- |
| Web Runtime     | Traditional Websites |
| API Runtime     | APIs                 |
| SPA Runtime     | Frontend Hydration   |
| Live Runtime    | Reactive UI          |
| Console Runtime | CLI                  |
| Worker Runtime  | Queues               |
| Edge Runtime    | Distributed Systems  |

---

# 17. Extension System

VoltStack soporta extensibilidad mediante:

* service providers,
* contracts,
* plugins,
* macros,
* runtime hooks,
* middleware pipelines.

---

# 18. Performance Strategy

VoltStack está diseñado para:

* FrankenPHP
* RoadRunner
* Swoole
* ReactPHP

y runtimes persistentes.

---

# 18.1 Performance Goals

Objetivos:

* mínimo boot time,
* mínimo memory footprint,
* lazy loading,
* runtime persistence,
* concurrent execution,
* streaming rendering.

---

# 19. Security Model

La arquitectura debe soportar:

* CSP
* CSRF
* XSS protection
* secure hydration
* signed payloads
* encrypted state
* runtime validation

---

# 20. Long-Term Architecture Vision

VoltStack busca convertirse en:

* un runtime PHP moderno,
* un framework fullstack progresivo,
* un ecosistema desacoplado,
* y una plataforma cloud-native preparada para sistemas distribuidos.

---

# 21. Architecture Motto

> “Simple at startup. Infinite at scale.”
