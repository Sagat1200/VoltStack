# Validation Architecture

Subsistema: `Quantum\Validation`

Este documento describe la arquitectura interna actual del sistema de validacion de VoltStack.

Si `1-Validation_Context.md` responde que es este subsistema y por que existe, este archivo describe como esta organizado internamente, como fluye la ejecucion y donde deben vivir las futuras ampliaciones.

## 1. Objetivo Arquitectonico

La arquitectura de `Quantum\Validation` busca equilibrar cuatro necesidades:

1. un motor pequeno y entendible
2. soporte dual para reglas string y reglas objeto
3. capacidad de crecer hacia reglas dependientes y declarativas
4. bajo acoplamiento con el resto del framework

La idea central es sencilla:

- `Validator` coordina
- las reglas encapsulan comportamiento
- el contexto transporta estado runtime
- la API `Rule` aporta ergonomia
- helpers y abstracciones internas reducen duplicacion

## 2. Vista General

La arquitectura actual puede resumirse asi:

```txt
Consumer
    ↓
ValidatorInterface
    ↓
Validator
    ↓
Rule normalization
    ↓
Field target resolution
    ↓
String rules or RuleInterface objects
    ↓
ValidationRuleContext
    ↓
Errors / Validated Data
```

Para reglas fluidas y declarativas existe una segunda vista:

```txt
Consumer
    ↓
Rule factory
    ↓
Rule objects / Declarative conditions
    ↓
Validator
    ↓
ValidationRuleContext
    ↓
Error rendering
```

## 3. Modulos Internos

Hoy `Quantum\Validation` se organiza en varios grupos de piezas.

### 3.1 Capa Publica

Piezas publicas principales:

- `ValidatorInterface`
- `Validator`
- `RuleInterface`
- `Rule`
- `ValidationRuleContext`
- `ValidationException`

Estas clases forman la cara principal del subsistema.

### 3.2 Capa De Reglas

La carpeta `Rules/` contiene:

- reglas atomicas simples
- reglas semanticas
- reglas dependientes
- clases abstractas de soporte
- traits de reutilizacion

Esta capa concentra la mayor parte del comportamiento especifico de validacion.

### 3.3 Capa De Condiciones Declarativas

La carpeta `Conditions/` contiene la DSL declarativa moderna.

Su responsabilidad es modelar condiciones reutilizables que luego pueden convertirse en reglas condicionales.

Piezas actuales:

- `WhenFieldBuilder`
- `FieldValueCondition`
- `FieldStateCondition`
- `CompositeCondition`
- `ChainedWhenBuilder`
- `DeclarativeConditionInterface`
- `BuildsDeclarativeRules`

### 3.4 Capa De Soporte Interno

Existen helpers internos reutilizados por varias reglas:

- `AbstractConditionalRule`
- `AbstractDependentFieldsRule`
- `ResolvesDependentFields`
- otros concerns pequenos como medicion de valor

Esta capa evita que `Validator` se convierta en un contenedor de logica especializada.

## 4. Principios De Arquitectura

La arquitectura actual sigue estos principios.

### 4.1 Core Coordinator

`Validator` es un coordinador, no una mega-clase de dominio.

Debe encargarse de:

- iteracion
- orquestacion
- mensajes
- errores
- control de flujo

Pero no debe absorber toda la logica semantica de cada regla nueva.

### 4.2 Rule-Oriented Behavior

La logica de cada regla orientada a objetos debe vivir en su propia clase.

Esto permite:

- pruebas mas precisas
- menor acoplamiento
- extensibilidad
- mejor lectura de la API

### 4.3 Context-Driven Runtime

Las reglas reciben estado por `ValidationRuleContext` en lugar de depender de servicios globales.

Esto hace que las reglas sean:

- mas portables
- mas faciles de probar
- menos acopladas al framework completo

### 4.4 Progressive Internal Abstractions

Las abstracciones internas solo se introducen cuando eliminan duplicacion real.

Por eso hoy existen:

- `AbstractConditionalRule` para reglas condicionales
- `AbstractDependentFieldsRule` para reglas que dependen de varios campos
- `ResolvesDependentFields` para wildcards y labels

## 5. Componentes Clave

### 5.1 `ValidatorInterface`

Es el contrato publico del motor.

Responsabilidades del contrato:

- definir `validate(...)`
- soportar `after(...)`
- soportar `stopOnFirstFailure(...)`

El contrato permite usar o sustituir la implementacion del validador sin acoplar consumidores a detalles internos.

### 5.2 `Validator`

`Validator` es el runtime principal del subsistema.

Responsabilidades actuales:

- recibir `data`, `rules`, `messages` y `attributes`
- normalizar reglas heterogeneas
- resolver targets concretos de campos
- ejecutar reglas string
- ejecutar objetos `RuleInterface`
- construir mensajes finales
- acumular errores
- lanzar `ValidationException` cuando corresponde
- ejecutar callbacks `after`

`Validator` es hoy el unico punto donde conviven ambos modelos:

- reglas string clasicas
- reglas objeto modernas

### 5.3 `RuleInterface`

Es el contrato base para reglas objeto.

Dos metodos lo definen:

- `name()`
- `validate(ValidationRuleContext $context)`

La arquitectura depende de que toda regla objeto pueda:

- identificarse
- ejecutarse con un contexto uniforme

### 5.4 `ValidationRuleContext`

Es el puente entre el motor y las reglas.

Contiene:

- el patron original del campo
- el campo concreto resuelto
- el valor actual
- la marca de presencia
- el `payload` completo
- los labels de atributos
- el nombre normalizado de la regla
- el callback de fallo

Ademas controla dos aspectos del flujo:

- `fail()` para registrar un error
- `skipRemainingRules()` para cortar el resto de validaciones del campo

### 5.5 `ValidationException`

Representa el resultado fallido consolidado.

Arquitectonicamente cumple dos funciones:

- encapsular el mapa de errores
- acoplar el subsistema con una respuesta HTTP 422 sin que las reglas tengan que conocer HTTP

## 6. Arquitectura Del Flujo De Validacion

La secuencia actual del runtime puede representarse asi:

```txt
validate(data, rules, messages, attributes)
    ↓
reset internal state
    ↓
for each field pattern
    ↓
normalize field rules
    ↓
resolve concrete targets
    ↓
for each rule in order
    ↓
string rule ? internal branch : object rule branch
    ↓
register error or continue
    ↓
after callbacks
    ↓
validated data or ValidationException
```

### 6.1 Normalizacion

Antes de ejecutar, el motor normaliza la definicion de reglas por campo.

Esto le permite aceptar entradas mixtas como:

```php
[
    'email' => 'required|email',
    'status' => [Rule::in('draft', 'published')],
]
```

### 6.2 Resolucion De Targets

Si un campo contiene wildcards, el motor resuelve instancias concretas.

Ejemplo:

```php
'items.*.value' => [Rule::confirmed('items.*.value_repeat')]
```

Durante la ejecucion puede transformarse conceptualmente en:

```txt
items.0.value
items.1.value
items.2.value
```

Cada target se valida como una unidad independiente.

### 6.3 Rama De Reglas String

Las reglas string siguen estando implementadas directamente en el validador.

Esto cubre:

- compatibilidad con sintaxis clasica
- reglas de bajo coste semantico
- comportamiento legacy del sistema

La desventaja arquitectonica es que esta rama concentra mas logica dentro de `Validator`.

### 6.4 Rama De Reglas Objeto

Cuando la regla implementa `RuleInterface`, `Validator`:

1. construye un `ValidationRuleContext`
2. invoca `validate($context)`
3. respeta `fail()` y `skipRemainingRules()`

Esta rama es la direccion preferida para crecer.

## 7. Arquitectura De Mensajes

La produccion de mensajes esta distribuida entre:

- `Validator`, para reglas string y renderizado final
- reglas objeto, para decidir placeholders y semantica del fallo
- helpers internos, para labels y resolucion de dependencias

### 7.1 Elementos Del Sistema De Mensajes

El sistema actual soporta:

- mensajes por campo
- mensajes por regla
- placeholders como `:attribute`, `:other`, `:value`, `:values`
- aliases via `$attributes`

### 7.2 Decision Arquitectonica

El texto final del error no lo determina solo la regla.

Se reparte asi:

- la regla sabe por que falla
- `Validator` sabe como renderizar el mensaje final

Este reparto evita duplicar toda la capa de rendering en cada regla.

## 8. Arquitectura De Reglas

Las reglas actuales pueden agruparse en cuatro familias.

### 8.1 Reglas Atomicas

Ejemplos:

- `RequiredRule`
- `StringRule`
- `EmailRule`
- `NumericRule`
- `UuidRule`

Caracteristicas:

- una responsabilidad concreta
- poca o nula dependencia de otros campos
- implementacion directa

### 8.2 Reglas Semanticas

Ejemplos:

- `AcceptedRule`
- `DeclinedRule`
- `NullableRule`
- `PresentRule`
- `ProhibitedRule`
- `SameRule`
- `ConfirmedRule`

Caracteristicas:

- expresan intencion de negocio mejor que la sintaxis string
- pueden apoyarse en contexto adicional
- ayudan a construir una API mas legible

### 8.3 Reglas Dependientes

Ejemplos:

- `RequiredIfRule`
- `RequiredUnlessRule`
- `AcceptedIfRule`
- `DeclinedIfRule`
- `ProhibitedIfRule`
- `RequiredWithRule`
- `RequiredWithoutRule`

Caracteristicas:

- dependen de uno o varios campos
- suelen requerir placeholders como `:other`, `:value`, `:values`
- necesitan resolver wildcards correctamente

### 8.4 Reglas Basadas En Callback

`CallbackRule` permite inyectar logica ad hoc sin ampliar el core de inmediato.

Arquitectonicamente es util para:

- prototipado
- extensiones locales
- casos donde aun no merece crearse una regla dedicada

## 9. Abstracciones Internas Reutilizables

### 9.1 `AbstractConditionalRule`

Es la base comun para reglas como:

- `RequiredIfRule`
- `RequiredUnlessRule`
- `AcceptedIfRule`
- `DeclinedIfRule`
- `ProhibitedIfRule`

Su papel arquitectonico es unificar:

- condiciones booleanas
- condiciones por `Closure`
- condiciones por otro campo
- condiciones declarativas
- placeholders y mensajes dependientes

Con esto se evita duplicar el mismo algoritmo en varias reglas.

### 9.2 `AbstractDependentFieldsRule`

Es la base para reglas que trabajan con varios campos dependientes.

Su papel es concentrar:

- resolucion de campos dependientes
- labels legibles
- chequeos de vacio y presencia

### 9.3 `ResolvesDependentFields`

Es el helper clave para wildcards y acceso por path.

Se reutiliza tanto en reglas como en condiciones declarativas.

Responsabilidades:

- resolver un campo dependiente a partir del patron real
- resolver listas de campos
- leer valores por dot-path
- comprobar existencia de paths
- convertir campos en labels de atributo

Este trait es una pieza central de la arquitectura actual.

## 10. Arquitectura De La DSL Declarativa

La DSL declarativa es una subarquitectura dentro de `Quantum\Validation`.

Su objetivo es desacoplar la semantica condicional de las reglas concretas.

### 10.1 Entrada

La puerta de entrada es:

```php
Rule::when('status')
```

Esto devuelve un `WhenFieldBuilder`.

### 10.2 Condiciones Por Valor

`WhenFieldBuilder` produce `FieldValueCondition` para expresiones como:

- `is(...)`
- `isNot(...)`
- `in(...)`
- `notIn(...)`

Estas condiciones modelan comparaciones sobre un campo.

### 10.3 Condiciones Por Estado

Para presencia y vacio se usa `FieldStateCondition`.

Estados actuales:

- `exists`
- `missing`
- `empty`
- `filled`

### 10.4 Condiciones Compuestas

La composicion se modela con `CompositeCondition`.

Soporta:

- `allOf(...)`
- `anyOf(...)`

Tambien existe un builder encadenado `ChainedWhenBuilder` para:

- `andWhen(...)`
- `orWhen(...)`

### 10.5 Salida De La DSL

Una condicion declarativa puede terminar en una regla concreta:

```php
Rule::when('status')->is('draft')->thenProhibited()
```

o puede usarse como condicion reusable:

```php
$published = Rule::when('status')->is('published');

Rule::acceptedIf($published);
```

Esta separacion entre condicion y regla es una decision arquitectonica importante, porque evita mezclar comparacion con accion de validacion.

## 11. Dependencias Permitidas

`Quantum\Validation` debe seguir siendo un subsistema de bajo acoplamiento.

Dependencias razonables hoy:

- contratos internos del propio paquete
- excepciones base del framework
- utilidades nativas de PHP

Dependencias que deberian evitarse:

- session
- auth
- database
- orm
- rendering
- transporte HTTP especifico dentro de las reglas

La unica concesion visible hoy es que `ValidationException` se apoya en una excepcion HTTP base para expresar el resultado 422.

## 12. Puntos De Extension

La arquitectura ya ofrece varios puntos de extension naturales.

### 12.1 Nuevas Reglas Objeto

Es la extension preferida.

Cuando una nueva regla tiene semantica propia, deberia implementarse como clase dedicada.

### 12.2 Nuevas Factories En `Rule`

Cuando una regla ya existe o una familia necesita mejor ergonomia, se puede ampliar `Rule`.

### 12.3 Nuevas Condiciones Declarativas

La DSL puede crecer mediante nuevas implementaciones de `DeclarativeConditionInterface`.

### 12.4 Nuevas Bases Internas

Solo deben aparecer si reducen duplicacion real entre varias reglas.

## 13. Riesgos Arquitectonicos Actuales

La arquitectura actual funciona, pero tiene riesgos claros.

### 13.1 Logica Residual En `Validator`

La rama de reglas string todavia concentra bastante logica interna.

Riesgo:

- crecimiento del archivo
- mayor coste de mantenimiento
- mezcla de orquestacion y comportamiento

### 13.2 Multiplicacion De Variantes Condicionales

A medida que crecen `requiredIf`, `acceptedIf`, `when`, `allOf`, `anyOf` y similares, existe riesgo de dispersar semantica.

Mitigacion:

- mantener la DSL sobre contratos comunes
- seguir reutilizando abstracciones como `AbstractConditionalRule`

### 13.3 Mensajeria Distribuida

La generacion de mensajes esta repartida entre varias capas.

Eso da flexibilidad, pero tambien puede introducir inconsistencias si no se documenta bien.

## 14. Direccion Arquitectonica Recomendada

La evolucion recomendada del subsistema es:

1. mover gradualmente mas comportamiento a reglas objeto
2. conservar `Validator` como coordinador
3. mantener la DSL declarativa como capa separada de las reglas
4. reforzar contratos de extension antes de abrir registros globales de reglas
5. documentar mejor las convenciones de mensajes, wildcards y dependencias

## 15. Resumen Estructural

La arquitectura actual de `Quantum\Validation` se apoya en este reparto:

```txt
Validator
    -> orchestration
    -> normalization
    -> message rendering
    -> error aggregation

RuleInterface rules
    -> validation behavior

ValidationRuleContext
    -> runtime state transport

Rule
    -> fluent public API

Conditions/*
    -> declarative conditional DSL

Abstract rules + concerns
    -> shared internal mechanics
```

Ese reparto es suficientemente pequeno para seguir siendo mantenible y suficientemente flexible para soportar la evolucion que ya empezo en la API fluida.

## 16. Documentos Relacionados

- `Docs/Validation/1-Validation_Context.md`
- `Docs/Validation/3-Roadmap.md`
- `Docs/Validation/6-Process.md`
- `Docs/Validation/7-Tests.md`
- `Docs/Validation/8-Use_Examples.md`
