PROJECT_CONTEXT.md
VoltStack Framework
Project Context

1. Vision

VoltStack es un framework PHP modular, progresivo y de alto rendimiento diseñado para construir desde sitios web simples hasta plataformas empresariales complejas, APIs distribuidas y aplicaciones fullstack modernas.

El objetivo principal es combinar:

la simplicidad y productividad de frameworks como Laravel,
la modularidad y escalabilidad de arquitecturas enterprise,
y una integración SPA moderna desacoplada del backend.

VoltStack busca convertirse en un ecosistema PHP moderno donde el desarrollador pueda comenzar con una aplicación mínima y evolucionarla progresivamente sin cambiar de stack.

1. Problem Statement

Los frameworks PHP modernos suelen presentar uno o más de estos problemas:

Frameworks demasiado monolíticos

Muchos frameworks cargan demasiadas dependencias y componentes incluso cuando el proyecto solo necesita funcionalidades básicas.

Acoplamiento excesivo

Algunos sistemas mezclan:

HTTP
Routing
Controllers
ORM
Sessions
Frontend
Rendering

haciendo difícil escalar, reemplazar o reutilizar componentes.

Integración SPA inconsistente

El soporte para:

React
Vue
Svelte
Solid

normalmente depende de herramientas externas o arquitecturas no unificadas.

Backend no preparado para reactividad moderna

Los frameworks tradicionales fueron diseñados para:

Request → Response HTML

pero no para:

hydration
streaming
partial updates
realtime state synchronization
component protocols
resumability
concurrent rendering
Escalabilidad limitada por diseño

Muchos frameworks no diferencian correctamente:

páginas simples
APIs
microservicios
plataformas distribuidas
aplicaciones empresariales

provocando sobreingeniería o limitaciones estructurales.

1. Goals
3.1 Modularidad Real

Cada subsistema debe poder instalarse y funcionar independientemente.

Ejemplo:

quantum/routing

no debe depender de:

quantum/database
quantum/auth
quantum/session
3.2 Backend Ligero

El núcleo inicial debe ser extremadamente ligero.

Una instalación mínima debe incluir únicamente:

bootstrap
container
config
routing
http
controllers
actions
views
components
exceptions
validation
console
3.3 Escalabilidad Progresiva

El framework debe crecer según necesidades:

Tipo de aplicación Paquetes requeridos
Página simple Core mínimo
API HTTP + Routing + Validation
SPA quantum/spa
SaaS Auth + Sessions + Queue + Storage
Enterprise Modules + Concurrency + Process
3.4 Fullstack Moderno

VoltStack debe soportar oficialmente:

React
Vue
Svelte
Solid
Runtime reactivo propio tipo Livewire

todo mediante adaptadores oficiales.

3.5 Arquitectura desacoplada

Todos los paquetes deben comunicarse mediante:

contratos
interfaces
pipelines
eventos
protocolos

y nunca mediante dependencias rígidas.

3.6 Alto rendimiento

VoltStack debe diseñarse pensando en:

FrankenPHP
RoadRunner
Swoole
ReactPHP
entornos persistentes
concurrencia
streaming
procesos asíncronos
4. Non Goals

VoltStack NO busca:

Ser un clon exacto de Laravel

Se tomará inspiración de:

Laravel
Symfony
Spiral
Livewire
Next.js
Nuxt
Phoenix
Rails

pero la arquitectura será propia.

Depender completamente de JavaScript

El backend PHP debe ser completamente funcional incluso sin SPA.

Acoplar frontend y backend

El frontend será desacoplado mediante adaptadores.

Obligar al uso de ORM

El sistema de datos debe ser intercambiable.

1. Core Philosophy
5.1 Progressive Architecture

El desarrollador empieza simple y escala progresivamente.

5.2 Everything Is Replaceable

Todo componente puede reemplazarse:

router
renderer
cache
storage
auth
transport
rendering engine
5.3 Backend First

El backend define:

estado
lógica
protocolos
rendering contracts

El frontend hidrata y representa.

5.4 Runtime Driven

VoltStack será impulsado por runtime contracts y pipelines.

5.5 Minimal by Default

Instalación mínima:

composer create-project voltstack/app

Debe generar una aplicación funcional extremadamente ligera.

1. Core Architecture
Foundation Layer

Contiene:

bootstrap
config
container
console

Paquetes:

quantum/bootstrap
quantum/config
quantum/container
quantum/console
HTTP Layer

Contiene:

requests
responses
middleware
routing
controllers
kernel

Paquetes:

quantum/http
quantum/http-kernel
quantum/routing
quantum/controllers
Application Layer

Contiene:

actions
validation
exceptions
concurrency
process

Paquetes:

quantum/actions
quantum/validation
quantum/exceptions
quantum/concurrency
quantum/process
Presentation Layer

Contiene:

views
components
SPA rendering
hydration

Paquetes:

quantum/view
quantum/components
quantum/spa
Optional Layer

Paquetes instalables:

quantum/auth
quantum/session
quantum/cookies
quantum/database
quantum/events
quantum/queue
quantum/storage
quantum/mail
quantum/modules
7. SPA Strategy

VoltStack utilizará un protocolo unificado para SPA.

Backend

PHP define:

screens
components
props
actions
transitions
effects
Frontend

El adaptador hidrata:

React
Vue
Svelte
Solid
Runtime Protocol

El protocolo será independiente del framework frontend.

Ejemplo:

{
  "screen": "Dashboard",
  "components": [],
  "state": {},
  "effects": [],
  "transitions": {}
}
8. Official SPA Packages
Core
quantum/spa
Adapters
quantum/spa-react
quantum/spa-vue
quantum/spa-svelte
quantum/spa-solid
Reactive Runtime
quantum/live

Sistema reactivo propio inspirado en:

Livewire
Phoenix LiveView
Hotwire
Qwik resumability
React Server Components
9. Runtime Goals

VoltStack debe soportar:

SSR
SPA hydration
streaming
resumability
partial hydration
islands architecture
concurrent rendering
websocket state sync
edge rendering
10. Scaling Strategy
Small Applications
routing + controllers + views
APIs
routing + validation + actions
Realtime Apps
quantum/live
Enterprise Platforms
modules + queue + events + storage + concurrency
Distributed Systems
process + concurrency + async pipelines
11. Developer Experience

VoltStack debe ofrecer:

DX moderna
comandos claros
scaffolding limpio
tipado fuerte
contracts consistentes
arquitectura predecible
componentes desacoplados
documentación enterprise-grade
12. Initial Package List
Package Responsibility
quantum/bootstrap Bootstrapping
quantum/config Configuración
quantum/container Dependency Injection
quantum/http HTTP Abstractions
quantum/http-kernel HTTP Lifecycle
quantum/routing Routing
quantum/controllers Controllers
quantum/actions Application Actions
quantum/validation Validation
quantum/exceptions Exception Handling
quantum/console CLI
quantum/cache Cache
quantum/view Views
quantum/components UI Components
quantum/spa SPA Protocol
quantum/concurrency Concurrent Execution
quantum/process Process Management
13. Future Package List
Package Responsibility
quantum/auth Authentication
quantum/session Sessions
quantum/cookies Cookies
quantum/database Database Layer
quantum/orm ORM
quantum/events Event System
quantum/queue Queues
quantum/storage Filesystem
quantum/mail Mail
quantum/modules Modular Applications
quantum/realtime WebSockets
quantum/testing Testing
quantum/telemetry Metrics & Tracing
quantum/cloud Cloud Runtime
quantum/devtools Developer Tools
14. Long-Term Vision

VoltStack busca convertirse en:

un framework PHP fullstack moderno,
un runtime progresivo,
un ecosistema desacoplado,
y una plataforma preparada para aplicaciones distribuidas y cloud-native.
15. Project Motto

“Build simple. Scale infinitely.”
