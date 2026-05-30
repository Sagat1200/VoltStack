# 05_SPA_PROTOCOL.md

# VoltStack Framework

## SPA Protocol

---

# 1. Overview

El SPA Protocol de VoltStack es un protocolo universal de comunicación entre el backend PHP y runtimes frontend modernos.

El protocolo define cómo:

* el backend describe pantallas,
* componentes,
* estados,
* efectos,
* navegación,
* eventos,
* y rendering,

para que cualquier adaptador frontend pueda hidratarlo.

---

# 2. Protocol Philosophy

El protocolo SPA está basado en principios:

* backend-first,
* frontend-agnostic,
* runtime-driven,
* transport-independent,
* reactive-ready,
* resumable-ready,
* streaming-capable.

---

# 3. Main Goals

---

## 3.1 Frontend Independence

El backend NO debe depender de:

* React,
* Vue,
* Svelte,
* Solid.

El frontend es únicamente un adaptador.

---

## 3.2 Unified Runtime

Todos los runtimes SPA consumen el mismo protocolo.

---

## 3.3 Server Driven UI

PHP controla:

* componentes,
* navegación,
* estado,
* efectos,
* rendering contracts.

---

## 3.4 Progressive Hydration

El protocolo debe soportar:

* full hydration,
* partial hydration,
* islands,
* resumability,
* streaming.

---

## 3.5 Realtime Ready

Compatible con:

* websocket sync,
* partial updates,
* live runtime,
* state synchronization.

---

# 4. Core Architecture

```txt id="3skd0q"
PHP Runtime
    ↓
SPA Protocol Serializer
    ↓
JSON Runtime Payload
    ↓
Frontend Adapter
    ↓
Hydration Runtime
    ↓
Reactive UI
```

---

# 5. Protocol Layers

El protocolo se divide en capas.

---

# 5.1 Screen Layer

Representa una pantalla completa.

---

# 5.2 Component Layer

Representa componentes visuales.

---

# 5.3 State Layer

Representa estado serializable.

---

# 5.4 Effects Layer

Representa efectos del runtime.

---

# 5.5 Transition Layer

Representa navegación y transiciones.

---

# 5.6 Event Layer

Representa eventos frontend/backend.

---

# 6. SPA Runtime Flow

```txt id="8qj4r9"
Request
    ↓
Controller
    ↓
Screen Builder
    ↓
Protocol Serializer
    ↓
JSON Payload
    ↓
Frontend Adapter
    ↓
Hydration
```

---

# 7. Screen Definition

El backend define Screens.

---

# 7.1 Example

```php id="xq5s2k"
return Screen::make('Dashboard')
    ->title('Admin Dashboard')
    ->state([
        'users' => 120,
    ])
    ->component('StatsCard', [
        'label' => 'Users',
        'value' => 120,
    ]);
```

---

# 7.2 Screen Responsibilities

Una Screen define:

* metadata,
* state,
* layout,
* components,
* transitions,
* effects.

---

# 8. Protocol Payload Structure

Payload base:

```json id="2a7fpk"
{
  "screen": {},
  "state": {},
  "components": [],
  "effects": [],
  "transitions": {},
  "meta": {}
}
```

---

# 9. Screen Payload

---

# 9.1 Structure

```json id="o33ydv"
{
  "screen": {
    "name": "Dashboard",
    "title": "Admin Dashboard",
    "layout": "admin"
  }
}
```

---

# 9.2 Screen Metadata

Puede incluir:

* route
* locale
* permissions
* auth state
* tenant
* rendering mode

---

# 10. Component Payload

Los componentes son backend-defined.

---

# 10.1 Component Structure

```json id="itjlwm"
{
  "id": "stats-card-1",
  "type": "StatsCard",
  "props": {},
  "slots": {},
  "children": []
}
```

---

# 10.2 Component Responsibilities

Los componentes pueden definir:

* props
* state
* events
* actions
* slots
* transitions

---

# 10.3 Nested Components

```json id="tjlwm4"
{
  "type": "Dashboard",
  "children": [
    {
      "type": "StatsCard"
    }
  ]
}
```

---

# 11. State Layer

El protocolo incluye estado serializable.

---

# 11.1 State Example

```json id="jlwm5r"
{
  "state": {
    "user": {
      "id": 1,
      "name": "Francisco"
    }
  }
}
```

---

# 11.2 State Goals

El estado debe ser:

* serializable,
* resumable,
* diffable,
* immutable-friendly.

---

# 12. Effects Layer

Los Effects representan instrucciones runtime.

---

# 12.1 Effect Examples

* redirect
* notification
* modal
* toast
* focus
* scroll
* refresh

---

# 12.2 Effect Payload

```json id="jlwm7p"
{
  "effects": [
    {
      "type": "toast",
      "message": "User created"
    }
  ]
}
```

---

# 13. Transition Layer

Representa navegación SPA.

---

# 13.1 Transition Example

```json id="jlwm8u"
{
  "transitions": {
    "navigate": "/dashboard",
    "replace": false,
    "preserveState": true
  }
}
```

---

# 13.2 Supported Transitions

| Transition | Description      |
| ---------- | ---------------- |
| navigate   | navegación       |
| replace    | replaceState     |
| back       | historial        |
| refresh    | reload parcial   |
| modal      | navegación modal |

---

# 14. Event Layer

El protocolo soporta eventos bidireccionales.

---

# 14.1 Event Structure

```json id="jlwm9z"
{
  "event": {
    "name": "user.created",
    "payload": {}
  }
}
```

---

# 14.2 Event Types

| Type              | Description     |
| ----------------- | --------------- |
| UI Events         | clicks          |
| Runtime Events    | hydration       |
| Live Events       | websocket       |
| Navigation Events | routing         |
| State Events      | synchronization |

---

# 15. Hydration System

La hidratación conecta backend y frontend.

---

# 15.1 Hydration Flow

```txt id="jlwmaw"
Serialized Payload
    ↓
Frontend Adapter
    ↓
Component Registry
    ↓
Component Resolver
    ↓
Reactive Runtime
```

---

# 15.2 Component Registry

Cada adaptador debe registrar componentes.

Ejemplo:

```ts id="jlwmbx"
registry.register('StatsCard', StatsCard)
```

---

# 16. Official Frontend Adapters

---

# 16.1 React Adapter

Paquete:

```txt id="jlwmcy"
quantum/spa-react
```

---

# 16.2 Vue Adapter

Paquete:

```txt id="jlwmde"
quantum/spa-vue
```

---

# 16.3 Svelte Adapter

Paquete:

```txt id="jlwmef"
quantum/spa-svelte
```

---

# 16.4 Solid Adapter

Paquete:

```txt id="jlwmfg"
quantum/spa-solid
```

---

# 17. Rendering Modes

---

# 17.1 Full SPA

Hydration completa.

---

# 17.2 SSR + Hydration

Server rendering + hydration.

---

# 17.3 Islands

Hydration parcial.

---

# 17.4 Streaming

Render incremental.

---

# 17.5 Resumability

Restauración de estado runtime.

---

# 18. Live Runtime Integration

El protocolo será compatible con:

```txt id="jlwmgh"
quantum/live
```

---

# 18.1 Live Payload Example

```json id="jlwmhi"
{
  "partial": true,
  "changes": [],
  "effects": []
}
```

---

# 18.2 Live Runtime Goals

* websocket sync
* realtime rendering
* partial updates
* streaming UI

---

# 19. Transport Layer

El protocolo debe ser independiente del transporte.

---

# 19.1 Supported Transports

| Transport    | Description |
| ------------ | ----------- |
| HTTP         | estándar    |
| WebSocket    | realtime    |
| SSE          | streaming   |
| Queue        | distributed |
| Edge Runtime | edge sync   |

---

# 20. Security Model

El protocolo debe soportar:

* signed payloads,
* encrypted state,
* CSRF protection,
* replay protection,
* runtime validation.

---

# 20.1 Signed Payload Example

```json id="jlwmij"
{
  "signature": "hash",
  "timestamp": 123456789
}
```

---

# 21. Component Runtime Contracts

Cada componente debe cumplir contratos runtime.

---

# 21.1 Component Contract

```php id="jlwmjk"
interface ComponentInterface
{
    public function render(): array;
}
```

---

# 21.2 Runtime Contract

```php id="jlwmkl"
interface RuntimeInterface
{
    public function hydrate(array $payload): void;
}
```

---

# 22. Protocol Serialization

El protocolo utiliza serialización runtime-aware.

---

# 22.1 Serialization Goals

* compact payloads
* diff-based updates
* lazy serialization
* streamable chunks

---

# 22.2 Serializer Pipeline

```txt id="jwlvml"
Screen
    ↓
Serializer
    ↓
Normalizer
    ↓
Transport Payload
```

---

# 23. Runtime Metadata

El payload puede incluir metadata.

---

# 23.1 Metadata Example

```json id="jlwmmn"
{
  "meta": {
    "locale": "en",
    "theme": "dark",
    "tenant": "acme"
  }
}
```

---

# 24. Long-Term SPA Vision

VoltStack busca crear:

* un protocolo universal frontend/backend,
* un runtime PHP reactivo moderno,
* una arquitectura fullstack desacoplada,
* y un ecosistema SPA backend-driven.

---

# 25. Protocol Motto

> “The backend defines the experience. The frontend hydrates the protocol.”
