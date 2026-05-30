# 06_COMPONENT_SYSTEM.md

# VoltStack Framework

## Component System

---

# 1. Overview

El sistema de componentes de VoltStack es una arquitectura backend-first diseñada para construir interfaces modernas, reactivas y desacopladas.

Los componentes son entidades runtime-aware capaces de:

* renderizar vistas,
* generar payloads SPA,
* sincronizar estado,
* emitir eventos,
* hidratar frontend,
* y operar en runtimes reactivos.

---

# 2. Core Philosophy

El sistema está basado en principios:

* backend-first UI,
* runtime-driven rendering,
* component contracts,
* reactive state,
* frontend agnostic architecture,
* theme-aware rendering,
* progressive hydration.

---

# 3. Main Goals

---

## 3.1 Frontend Independence

Los componentes NO pertenecen a React, Vue o Svelte.

El backend define:

* estructura,
* estado,
* props,
* eventos,
* rendering contracts.

---

## 3.2 Multi Runtime Support

Un componente debe funcionar en:

| Runtime      | Supported |
| ------------ | --------- |
| SSR          | yes       |
| SPA          | yes       |
| Live Runtime | yes       |
| Streaming    | yes       |
| Islands      | yes       |

---

## 3.3 Reactive Ready

El sistema debe soportar:

* state synchronization,
* partial updates,
* websocket sync,
* resumability,
* concurrent rendering.

---

## 3.4 Theme Awareness

Los componentes deben reaccionar al contexto visual.

Ejemplo:

* dark themes
* pastel themes
* bootstrap themes
* daisyUI themes
* custom design systems

---

# 4. Component Architecture

```txt id="8x9k2q"
Component Definition
    ↓
State Initialization
    ↓
Props Resolution
    ↓
Rendering Strategy
    ↓
Runtime Serialization
    ↓
Frontend Hydration
```

---

# 5. Component Structure

Un componente define:

* props,
* state,
* events,
* actions,
* slots,
* metadata,
* rendering strategy.

---

# 5.1 Conceptual Structure

```php id="5y9e1j"
final class ButtonComponent extends Component
{
    public function props(): array
    {
        return [];
    }

    public function state(): array
    {
        return [];
    }

    public function events(): array
    {
        return [];
    }

    public function render(): mixed
    {
        //
    }
}
```

---

# 6. Component Lifecycle

---

# 6.1 Lifecycle Flow

```txt id="r0k1xh"
Component Creation
    ↓
Props Resolution
    ↓
State Initialization
    ↓
Mount Lifecycle
    ↓
Render Lifecycle
    ↓
Hydration
    ↓
Updates
    ↓
Destroy Lifecycle
```

---

# 6.2 Lifecycle Hooks

| Hook      | Purpose              |
| --------- | -------------------- |
| boot      | early initialization |
| mount     | first mount          |
| hydrate   | restore state        |
| updating  | before update        |
| updated   | after update         |
| render    | rendering            |
| dehydrate | serialization        |
| destroy   | cleanup              |

---

# 7. Props System

Los Props representan datos externos.

---

# 7.1 Props Example

```php id="q9r7ev"
Button::make()
    ->props([
        'label' => 'Save',
        'variant' => 'primary',
    ]);
```

---

# 7.2 Props Goals

Los props deben ser:

* immutable-friendly,
* serializable,
* type-safe,
* runtime-aware.

---

# 8. State System

Los componentes poseen estado interno.

---

# 8.1 State Example

```php id="l7tx8n"
public function state(): array
{
    return [
        'loading' => false,
        'count' => 0,
    ];
}
```

---

# 8.2 State Goals

El estado debe soportar:

* hydration,
* partial updates,
* realtime sync,
* serialization,
* resumability.

---

# 9. Event System

Los componentes pueden emitir eventos.

---

# 9.1 Event Example

```php id="1w8e7z"
$this->emit('user.created');
```

---

# 9.2 Event Categories

| Type              | Description |
| ----------------- | ----------- |
| UI Events         | click       |
| Runtime Events    | hydrate     |
| State Events      | sync        |
| Live Events       | websocket   |
| Navigation Events | SPA routing |

---

# 10. Component States

VoltStack soporta estados tipados.

---

# 10.1 State Enum Example

```php id="u2f8k1"
enum ButtonComponentState: string
{
    case IDLE = 'idle';
    case LOADING = 'loading';
    case DISABLED = 'disabled';
}
```

---

# 10.2 State Goals

Los estados deben ser:

* explicit,
* type-safe,
* runtime-aware,
* serializable.

---

# 11. Component Events

Los eventos también pueden ser tipados.

---

# 11.1 Event Enum Example

```php id="z4j1ha"
enum ButtonComponentEvent: string
{
    case CLICK = 'click';
    case SUBMIT = 'submit';
    case LOADING = 'loading';
}
```

---

# 12. Rendering Strategies

Los componentes soportan múltiples renderers.

---

# 12.1 Rendering Modes

| Mode    | Description         |
| ------- | ------------------- |
| SSR     | server rendering    |
| SPA     | hydration rendering |
| Live    | reactive rendering  |
| Stream  | streaming           |
| Islands | partial hydration   |

---

# 12.2 Rendering Strategy Example

```php id="d8m4ve"
Button::make()
    ->renderer('spa');
```

---

# 13. View Components

VoltStack soporta rendering SSR.

---

# 13.1 SSR Example

```php id="q2x5md"
return view('components.button');
```

---

# 13.2 Responsibilities

SSR debe soportar:

* layouts,
* slots,
* fragments,
* sections,
* streaming.

---

# 14. SPA Components

Los componentes pueden serializarse a payloads SPA.

---

# 14.1 SPA Payload Example

```json id="j9w2ht"
{
  "type": "Button",
  "props": {
    "label": "Save"
  },
  "state": {
    "loading": false
  }
}
```

---

# 14.2 Hydration Goals

La hidratación debe soportar:

* resumability,
* lazy hydration,
* partial hydration,
* island hydration.

---

# 15. Live Runtime Components

Los componentes pueden operar persistentemente.

---

# 15.1 Live Runtime Flow

```txt id="k4j8vw"
Component Mount
    ↓
State Synchronization
    ↓
Realtime Event
    ↓
Partial Update
    ↓
Frontend Patch
```

---

# 15.2 Live Runtime Goals

* websocket sync,
* server-driven UI,
* reactive state,
* partial rendering,
* streaming updates.

---

# 16. Component Registry

Los componentes se registran centralmente.

---

# 16.1 Registry Example

```php id="m7x2ea"
ComponentRegistry::register(
    'button',
    ButtonComponent::class
);
```

---

# 16.2 Registry Responsibilities

* component discovery,
* component resolution,
* renderer binding,
* adapter integration.

---

# 17. Frontend Adapters

Cada framework frontend utiliza un adaptador.

---

# 17.1 Official Adapters

| Package            | Purpose |
| ------------------ | ------- |
| quantum/spa-react  | React   |
| quantum/spa-vue    | Vue     |
| quantum/spa-svelte | Svelte  |
| quantum/spa-solid  | Solid   |

---

# 17.2 Adapter Responsibilities

Los adaptadores deben:

* hidratar payloads,
* resolver componentes,
* sincronizar estado,
* manejar eventos.

---

# 18. Theme System

Los componentes son theme-aware.

---

# 18.1 Theme Goals

Soportar:

* daisyUI
* Bootstrap
* Tailwind
* custom design systems
* visual contracts

---

# 18.2 Theme Context Example

```php id="h9u4yk"
Button::make()
    ->theme('dark');
```

---

# 18.3 Theme Runtime

El runtime debe resolver:

* spacing
* colors
* contrast
* borders
* morphology
* variants

---

# 19. Visual Contracts

Los componentes definen contratos visuales.

---

# 19.1 Visual Contract Example

```php id="f1r8ca"
Button::make()
    ->variant('primary')
    ->size('lg')
    ->rounded();
```

---

# 19.2 Morphology Goals

El sistema visual debe adaptarse dinámicamente.

Ejemplo:

* themes oscuros → botones cuadrados,
* themes pastel → botones ovalados.

---

# 20. Slots System

Los componentes soportan slots.

---

# 20.1 Slot Example

```php id="g5n2wv"
Card::make()
    ->slot('header', $header)
    ->slot('body', $body);
```

---

# 21. Composition System

Los componentes son composables.

---

# 21.1 Nested Composition

```php id="t2q4jp"
Card::make([
    Button::make(),
    Input::make(),
]);
```

---

# 21.2 Composition Goals

* reusable UI,
* nested trees,
* recursive rendering,
* runtime-aware rendering.

---

# 22. Runtime Serialization

Los componentes deben serializarse.

---

# 22.1 Serialization Pipeline

```txt id="n6u1dx"
Component
    ↓
Normalizer
    ↓
Serializer
    ↓
Protocol Payload
```

---

# 22.2 Serialization Goals

* compact payloads,
* lazy serialization,
* partial serialization,
* diff updates.

---

# 23. Security Model

El sistema debe soportar:

* signed state,
* encrypted payloads,
* runtime validation,
* event validation,
* hydration protection.

---

# 24. Concurrent Rendering

VoltStack soportará rendering concurrente.

---

# 24.1 Concurrent Flow

```txt id="p4c9zr"
Component Tree
    ↓
Concurrent Scheduler
    ↓
Parallel Rendering
    ↓
Unified Output
```

---

# 24.2 Concurrent Goals

* streaming rendering,
* async rendering,
* partial updates,
* edge rendering.

---

# 25. Long-Term Vision

VoltStack busca construir:

* un runtime visual backend-first,
* un sistema de componentes universal,
* un motor reactivo moderno,
* y una arquitectura fullstack desacoplada.

---

# 26. Component System Motto

> “Components are runtime entities, not frontend fragments.”
