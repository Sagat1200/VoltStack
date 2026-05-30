# 03_PACKAGE_MAP.md

# VoltStack Framework

## Package Map

---

# 1. Overview

VoltStack está diseñado como un ecosistema modular compuesto por paquetes desacoplados.

Cada paquete tiene:

* una responsabilidad específica,
* límites arquitectónicos claros,
* dependencias controladas,
* contratos definidos,
* y capacidad de reemplazo.

Este documento define:

* paquetes oficiales,
* responsabilidades,
* dependencias permitidas,
* dependencias prohibidas,
* relaciones entre capas,
* y estrategias de escalabilidad.

---

# 2. Ecosystem Structure

El ecosistema se divide en:

```txt id="uvy2ij"
Foundation Packages
HTTP Packages
Application Packages
Presentation Packages
Infrastructure Packages
Runtime Packages
Optional Packages
Developer Packages
Cloud Packages
```

---

# 3. Foundation Packages

Responsables del arranque y núcleo técnico.

---

# 3.1 quantum/bootstrap

## Responsibility

Inicialización del framework.

## Features

* bootstrap runtime
* environment loading
* startup lifecycle
* provider bootstrapping

## Allowed Dependencies

```txt id="gggvjo"
quantum/config
quantum/container
```

## Forbidden Dependencies

```txt id="r0v8pz"
database
auth
session
routing
views
```

---

# 3.2 quantum/config

## Responsibility

Sistema de configuración.

## Features

* config repositories
* env integration
* runtime config
* cached config

## Allowed Dependencies

```txt id="zg9r5t"
none
```

---

# 3.3 quantum/container

## Responsibility

Dependency Injection Container.

## Features

* bindings
* singleton management
* contextual bindings
* autowiring
* service resolution

## Allowed Dependencies

```txt id="mp4lyf"
none
```

---

# 3.4 quantum/console

## Responsibility

CLI Runtime.

## Features

* artisan-like commands
* generators
* workers
* task runners

## Allowed Dependencies

```txt id="g8i8zf"
container
config
```

---

# 4. HTTP Packages

Responsables del ciclo HTTP.

---

# 4.1 quantum/http

## Responsibility

Abstracciones HTTP.

## Features

* Request
* Response
* Headers
* Uploaded Files
* Cookies
* Streams

## Allowed Dependencies

```txt id="l8lvwi"
none
```

---

# 4.2 quantum/http-kernel

## Responsibility

HTTP Runtime.

## Features

* middleware pipeline
* request lifecycle
* route dispatching
* exception integration

## Allowed Dependencies

```txt id="4q5ngm"
http
routing
container
exceptions
```

---

# 4.3 quantum/routing

## Responsibility

Sistema de rutas.

## Features

* route matching
* route groups
* middleware assignment
* named routes
* parameter binding

## Allowed Dependencies

```txt id="1qihbd"
http
```

## Forbidden Dependencies

```txt id="fz09fo"
database
session
auth
views
```

---

# 4.4 quantum/controllers

## Responsibility

Transport Layer.

## Features

* base controllers
* response helpers
* controller dispatching

## Allowed Dependencies

```txt id="5m4sm4"
routing
container
http
```

---

# 5. Application Packages

Responsables de lógica de negocio.

---

# 5.1 quantum/actions

## Responsibility

Business Logic Layer.

## Features

* reusable actions
* command execution
* transaction orchestration

## Allowed Dependencies

```txt id="0nchm7"
container
validation
```

## Forbidden Dependencies

```txt id="jlwm2x"
views
spa
http
```

---

# 5.2 quantum/validation

## Responsibility

Validation Runtime.

## Features

* validation rules
* validation pipelines
* async validation
* custom validators

## Allowed Dependencies

```txt id="lfdz74"
none
```

---

# 5.3 quantum/exceptions

## Responsibility

Exception Management.

## Features

* exception rendering
* exception mapping
* reporting
* logging integration

## Allowed Dependencies

```txt id="o0dy6z"
http
```

---

# 5.4 quantum/process

## Responsibility

Process Runtime.

## Features

* subprocess execution
* process pools
* daemons
* worker orchestration

## Allowed Dependencies

```txt id="qjxv1n"
container
```

---

# 5.5 quantum/concurrency

## Responsibility

Concurrent Runtime.

## Features

* fibers
* parallel execution
* async tasks
* concurrent pipelines

## Allowed Dependencies

```txt id="bjx7x9"
process
container
```

---

# 6. Presentation Packages

Responsables de rendering y frontend.

---

# 6.1 quantum/view

## Responsibility

View Rendering System.

## Features

* template engine
* layouts
* slots
* sections
* rendering contracts

## Allowed Dependencies

```txt id="n4mof3"
container
http
```

---

# 6.2 quantum/components

## Responsibility

Backend UI Components.

## Features

* components
* state
* events
* props
* rendering contracts

## Allowed Dependencies

```txt id="mtl59h"
view
container
```

---

# 6.3 quantum/spa

## Responsibility

SPA Runtime Protocol.

## Features

* hydration
* transitions
* effects
* protocol serialization
* frontend runtime bridge

## Allowed Dependencies

```txt id="z7xk8s"
http
components
routing
```

---

# 6.4 quantum/live

## Responsibility

Reactive Runtime.

## Features

* websocket synchronization
* partial updates
* reactive state
* streaming rendering

## Allowed Dependencies

```txt id="8ijbws"
spa
concurrency
http
```

---

# 7. Official SPA Adapter Packages

---

# 7.1 quantum/spa-react

## Responsibility

React adapter.

## Allowed Dependencies

```txt id="ma5odr"
spa
```

---

# 7.2 quantum/spa-vue

## Responsibility

Vue adapter.

## Allowed Dependencies

```txt id="rtcr0u"
spa
```

---

# 7.3 quantum/spa-svelte

## Responsibility

Svelte adapter.

## Allowed Dependencies

```txt id="36g0xg"
spa
```

---

# 7.4 quantum/spa-solid

## Responsibility

Solid adapter.

## Allowed Dependencies

```txt id="i7cyrr"
spa
```

---

# 8. Infrastructure Packages

Responsables de servicios externos.

---

# 8.1 quantum/database

## Responsibility

Database Abstraction Layer.

## Features

* drivers
* connections
* query builder
* transactions

## Allowed Dependencies

```txt id="n1y73r"
config
container
```

---

# 8.2 quantum/orm

## Responsibility

ORM Layer.

## Features

* models
* relations
* eager loading
* entity lifecycle

## Allowed Dependencies

```txt id="83jlwm"
database
validation
```

---

# 8.3 quantum/cache

## Responsibility

Cache Runtime.

## Features

* memory cache
* redis cache
* distributed cache
* tagged cache

## Allowed Dependencies

```txt id="tb4y1j"
config
container
```

---

# 8.4 quantum/storage

## Responsibility

Filesystem Runtime.

## Features

* local storage
* cloud storage
* streams
* file abstraction

## Allowed Dependencies

```txt id="5qq1y8"
config
container
```

---

# 8.5 quantum/queue

## Responsibility

Queue Runtime.

## Features

* jobs
* workers
* delayed execution
* distributed queues

## Allowed Dependencies

```txt id="t5b2lr"
process
concurrency
container
```

---

# 8.6 quantum/events

## Responsibility

Event System.

## Features

* events
* listeners
* broadcasting
* async listeners

## Allowed Dependencies

```txt id="4c7mng"
container
queue
```

---

# 8.7 quantum/mail

## Responsibility

Mail Runtime.

## Features

* SMTP
* API providers
* templated mail
* async delivery

## Allowed Dependencies

```txt id="t3lkj0"
view
queue
```

---

# 9. Security Packages

---

# 9.1 quantum/auth

## Responsibility

Authentication Runtime.

## Features

* guards
* authentication providers
* JWT
* token systems
* session auth

## Allowed Dependencies

```txt id="f6m8v5"
database
session
cookies
http
```

---

# 9.2 quantum/session

## Responsibility

Session Runtime.

## Features

* session storage
* flash data
* distributed sessions

## Allowed Dependencies

```txt id="04m8pc"
http
cache
```

---

# 9.3 quantum/cookies

## Responsibility

Cookie Runtime.

## Features

* encrypted cookies
* signed cookies
* cookie queues

## Allowed Dependencies

```txt id="huxc7f"
http
```

---

# 10. Modular Architecture Packages

---

# 10.1 quantum/modules

## Responsibility

Modular Application Runtime.

## Features

* isolated modules
* module providers
* module routes
* module assets
* module migrations

## Allowed Dependencies

```txt id="8q54pk"
container
routing
config
```

---

# 11. Developer Experience Packages

---

# 11.1 quantum/testing

## Responsibility

Testing Runtime.

## Features

* unit testing
* HTTP testing
* component testing
* SPA testing

---

# 11.2 quantum/devtools

## Responsibility

Developer Tools.

## Features

* profiling
* debug tools
* runtime inspector
* hydration inspector

---

# 11.3 quantum/telemetry

## Responsibility

Observability Runtime.

## Features

* tracing
* metrics
* distributed telemetry
* monitoring

---

# 12. Cloud Packages

---

# 12.1 quantum/cloud

## Responsibility

Cloud Runtime.

## Features

* distributed runtime
* edge execution
* deployment runtime
* scaling runtime

---

# 12.2 quantum/realtime

## Responsibility

Realtime Infrastructure.

## Features

* websockets
* distributed sync
* pub/sub
* realtime channels

---

# 13. Runtime Dependency Graph

```txt id="c6e8es"
bootstrap
    ↓
config
    ↓
container
    ↓
http
    ↓
routing
    ↓
http-kernel
    ↓
controllers
    ↓
actions
    ↓
renderer
    ↓
spa/live
```

---

# 14. Architectural Dependency Rules

---

## Core Rule

Las capas inferiores nunca deben depender de capas superiores.

---

## Example

Correcto:

```txt id="4m84yj"
routing → http
```

Incorrecto:

```txt id="ut1i6q"
http → routing
```

---

# 15. Package Installation Philosophy

VoltStack funciona bajo instalación progresiva.

---

## Minimal Website

```bash
composer require \
    quantum/bootstrap \
    quantum/http \
    quantum/routing \
    quantum/view
```

---

## API Runtime

```bash
composer require \
    quantum/http \
    quantum/routing \
    quantum/actions \
    quantum/validation
```

---

## SPA Runtime

```bash
composer require \
    quantum/spa \
    quantum/spa-react
```

---

## Enterprise Runtime

```bash
composer require \
    quantum/modules \
    quantum/concurrency \
    quantum/process \
    quantum/queue
```

---

# 16. Long-Term Ecosystem Vision

VoltStack evolucionará hacia:

* ecosystem-first architecture,
* cloud-native runtimes,
* distributed execution,
* server-driven UI,
* reactive backend systems,
* and enterprise modular platforms.

---

# 17. Package Map Motto

> “Every package has one responsibility. Every responsibility can scale independently.”
