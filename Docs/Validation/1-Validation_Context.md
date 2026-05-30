# Validation Context

Subsistema: `Quantum\Validation`

Documento de contexto del sistema de validacion de VoltStack.

Este archivo define el estado actual del subsistema, su rol dentro del framework, las capacidades disponibles hoy y los principios que deben guiar su evolucion.

## 1. Vision

El sistema de validacion de VoltStack debe ofrecer una capa pequeña, expresiva y desacoplada para validar datos de entrada en:

- peticiones HTTP
- formularios server-side
- acciones de aplicacion
- pipelines internos
- validacion de estructuras anidadas

La meta no es solo replicar una lista de reglas comunes, sino construir una API coherente que pueda crecer desde validacion simple basada en strings hasta reglas semanticas, contextuales y composables.

## 2. Rol Dentro Del Framework

En la arquitectura general de VoltStack, `Quantum\Validation` pertenece a la capa de aplicacion.

Su responsabilidad es:

- recibir un `payload` arbitrario
- aplicar reglas sobre campos simples o anidados
- producir mensajes de error consistentes
- devolver datos validados cuando no existen errores
- exponer una base reutilizable para integraciones futuras con HTTP, formularios, actions y componentes

No es responsabilidad de este subsistema:

- transportar la request HTTP
- renderizar formularios
- persistir modelos
- resolver autorizacion
- acoplarse a un ORM o capa de base de datos

## 3. Objetivos Del Subsistema

Los objetivos actuales del sistema de validacion son:

1. Mantener una API minima y predecible.
2. Soportar reglas string clasicas y reglas orientadas a objetos.
3. Permitir validacion de arrays y wildcards como `items.*.name`.
4. Ofrecer mensajes personalizables y alias de atributos.
5. Permitir reglas dependientes de otros campos.
6. Habilitar una DSL fluida y mas semantica a traves de `Rule`.
7. Permanecer desacoplado del resto del framework.

## 4. Estado Actual

Hoy el sistema ya dispone de una implementacion funcional y usable.

Las piezas principales son:

- `Validator`: motor de ejecucion principal.
- `ValidatorInterface`: contrato publico del validador.
- `RuleInterface`: contrato de reglas orientadas a objetos.
- `ValidationRuleContext`: contexto runtime que recibe cada regla.
- `ValidationException`: excepcion con errores consolidados.
- `Rule`: factory fluida para construir reglas semanticas.

Capacidades presentes hoy:

- reglas string y reglas objeto en la misma validacion
- reglas basicas de tipo, formato, tamano y contenido
- `present`, `required`, `nullable`, `prohibited`
- reglas dependientes como `requiredIf`, `requiredUnless`, `requiredWith`, `requiredWithout`
- reglas semanticas como `accepted`, `declined`, `same`, `confirmed`, `in`
- wildcards sobre arrays anidados
- mensajes personalizados por campo y por regla
- alias de atributos para mensajes
- `after()` para callbacks posteriores a la validacion
- `stopOnFirstFailure()` para corte temprano global
- una DSL declarativa basada en `Rule::when(...)`

## 5. Filosofia De Diseno

La validacion en VoltStack sigue estas ideas:

### 5.1 Small Core

El motor central debe seguir siendo relativamente pequeno.

La mayor parte de la expresividad debe vivir en:

- reglas reutilizables
- factories fluidas
- objetos de condicion
- helpers internos de resolucion

### 5.2 API Dual

VoltStack soporta dos estilos de uso:

1. reglas string para casos rapidos y compatibles con la sintaxis clasica
2. reglas objeto para extensibilidad, composicion y una API mas rica

Ambos estilos deben convivir sin friccion.

### 5.3 Context Over Globals

Las reglas objeto no dependen de estado global.

Todo lo necesario para validar se entrega mediante `ValidationRuleContext`, incluyendo:

- patron original
- campo concreto resuelto
- valor actual
- indicador de presencia
- `data` completo
- atributos legibles
- nombre normalizado de la regla

### 5.4 Progressive Expressiveness

La API debe permitir empezar con algo simple:

```php
['email' => 'required|email']
```

y evolucionar hacia algo mas semantico:

```php
[
    'status' => [Rule::in('draft', 'published')],
    'terms' => [Rule::accepted()],
    'password' => [Rule::confirmed()],
]
```

o incluso declarativo:

```php
[
    'review_reason' => [
        Rule::when('status')->is('rejected')->thenRequired(),
    ],
]
```

## 6. Componentes Principales

### 6.1 `Validator`

`Validator` es el orquestador del subsistema.

Se encarga de:

- normalizar reglas
- resolver campos concretos, incluidos wildcards
- ejecutar reglas string
- ejecutar reglas objeto
- registrar errores por campo
- aplicar reemplazos de mensajes
- decidir si debe detener la validacion por `bail` o `stopOnFirstFailure`

La firma central actual es:

```php
public function validate(
    array $data,
    array $rules,
    array $messages = [],
    array $attributes = []
): array;
```

Devuelve el array validado si todo pasa y lanza `ValidationException` cuando hay errores.

### 6.2 `RuleInterface`

Es el contrato para reglas objeto:

```php
interface RuleInterface
{
    public function name(): string;

    public function validate(ValidationRuleContext $context): void;
}
```

Esto permite crear reglas desacopladas del motor central.

### 6.3 `ValidationRuleContext`

Es el contexto runtime recibido por cada regla objeto.

Expone informacion clave como:

- `pattern()`
- `field()`
- `value()`
- `present()`
- `data()`
- `attributes()`
- `rule()`
- `fail()`
- `skipRemainingRules()`

`skipRemainingRules()` es especialmente importante para reglas como `nullable()`, donde el campo puede cortar el resto de reglas si el valor es `null`.

### 6.4 `Rule`

`Rule` es la puerta de entrada fluida y semantica del sistema.

Actualmente actua como:

- factory de reglas basicas
- factory de reglas dependientes
- punto de entrada a la DSL declarativa
- capa de ergonomia para firmas expresivas

Ejemplos:

```php
Rule::required();
Rule::email();
Rule::accepted();
Rule::requiredIf('status', 'published');
Rule::in('draft', 'published');
Rule::same('password_confirmation');
Rule::confirmed();
```

## 7. Capacidades Actuales De La API Fluida

La API `Rule` ya soporta varias familias de reglas.

### 7.1 Reglas Basicas

- `required()`
- `string()`
- `array()`
- `boolean()`
- `integer()`
- `numeric()`
- `date()`
- `email()`
- `url()`
- `json()`
- `uuid()`
- `ip()`
- `ipv4()`
- `ipv6()`
- `ascii()`
- `alphaDash()`
- `lowercase()`
- `uppercase()`

### 7.2 Reglas De Tamano O Formato

- `min()`
- `max()`
- `between()`
- `size()`
- `digits()`
- `regex()`
- `notRegex()`
- `startsWith()`
- `endsWith()`

### 7.3 Reglas Semanticas

- `accepted()`
- `declined()`
- `nullable()`
- `present()`
- `prohibited()`
- `same()`
- `confirmed()`
- `in(...)`

### 7.4 Reglas Dependientes

- `requiredIf(...)`
- `requiredUnless(...)`
- `requiredWith(...)`
- `requiredWithout(...)`
- `acceptedIf(...)`
- `declinedIf(...)`
- `prohibitedIf(...)`

Estas reglas aceptan distintos estilos segun el caso:

- booleanos
- `callable`
- nombre de otro campo
- condiciones declarativas

### 7.5 DSL Declarativa

La DSL declarativa actual parte de `Rule::when('field')`.

Comparadores por valor:

- `is(...)`
- `isNot(...)`
- `in(...)`
- `notIn(...)`

Estados del campo:

- `exists()`
- `missing()`
- `empty()`
- `filled()`

Derivacion de reglas:

- `required()`
- `accepted()`
- `declined()`
- `prohibited()`
- `thenRequired()`
- `thenAccepted()`
- `thenDeclined()`
- `thenProhibited()`

Composicion:

- `Rule::allOf(...)`
- `Rule::anyOf(...)`
- `andWhen(...)`
- `orWhen(...)`

Ejemplos:

```php
Rule::when('status')->is('draft')->thenProhibited();

Rule::when('delivery_mode')->isNot('auto')->thenRequired();

Rule::allOf(
    Rule::when('delivery_mode')->is('manual'),
    Rule::when('reviewer_id')->exists(),
)->thenRequired();
```

## 8. Modelo De Ejecucion

La validacion actual sigue este flujo conceptual:

1. El consumidor llama a `validate($data, $rules, $messages, $attributes)`.
2. El validador normaliza el conjunto de reglas por campo.
3. Si el campo contiene wildcards, resuelve los targets concretos.
4. Ejecuta cada regla en orden.
5. Si la regla es string, aplica la logica interna del validador.
6. Si la regla es objeto, crea un `ValidationRuleContext` y delega en la regla.
7. Si una regla falla, registra el mensaje y evalua `bail` o `stopOnFirstFailure`.
8. Si no hay errores, devuelve los datos.
9. Si hay errores, lanza `ValidationException`.

## 9. Mensajes Y Atributos

El sistema soporta:

- mensajes por campo y regla, por ejemplo `email.required`
- mensajes genericos por nombre de regla
- placeholders de reemplazo como `:attribute`, `:other`, `:value`, `:values`
- alias de atributos mediante el array `$attributes`

Esto permite desacoplar el nombre real del campo del texto mostrado al usuario.

## 10. Wildcards Y Campos Dependientes

Una de las capacidades mas relevantes del sistema actual es la resolucion de campos dependientes cuando existen wildcards.

Ejemplo conceptual:

```php
[
    'items.*.value' => [Rule::confirmed('items.*.value_repeat')],
]
```

Cuando se valida `items.0.value`, la regla debe resolver automaticamente el campo dependiente correspondiente como `items.0.value_repeat`.

Este comportamiento ya forma parte del subsistema actual y es una pieza importante de su diseno.

## 11. Fortalezas Del Estado Actual

El sistema ya tiene varios puntos fuertes:

- el nucleo es pequeno y facil de seguir
- la API `Rule` ya es bastante expresiva
- existe compatibilidad entre reglas string y reglas objeto
- la resolucion de wildcards ya cubre casos reales
- las reglas dependientes ya no estan limitadas a una sintaxis string rigida
- la DSL declarativa permite crecer hacia casos mas semanticos sin sobrecargar `Validator`

## 12. Limites Actuales

Aunque la base es solida, el subsistema todavia tiene limites claros.

Entre ellos:

- no existe integracion formal todavia con request objects o form objects
- no hay documentacion cerrada aun para arquitectura interna y roadmap
- faltan mas reglas avanzadas y familias completas de condiciones
- la internacionalizacion de mensajes todavia no esta formalizada
- aun no existe una capa publica de extensibilidad para registros globales de reglas
- el sistema esta enfocado en validacion sincrona y local

## 13. Direccion De Evolucion

La evolucion natural del subsistema deberia seguir estas lineas:

1. Consolidar la arquitectura interna del motor y de las reglas dependientes.
2. Completar la documentacion de arquitectura, proceso y ejemplos.
3. Expandir la DSL sin degradar la simplicidad del core.
4. Integrar validacion con HTTP, actions y formularios del framework.
5. Preparar una estrategia de extensibilidad publica y versionado semantico.

## 14. Posicion Dentro De VoltStack

`Quantum\Validation` es un subsistema transversal.

Aunque hoy puede usarse de forma aislada, esta pensado para convertirse en una pieza comun para:

- `HTTP`
- `Actions`
- formularios y DTOs
- componentes server-driven
- modulos de dominio

Su valor no esta solo en validar datos, sino en ofrecer una semantica comun de entrada y errores a traves del framework.

## 15. Resumen

Hoy VoltStack ya cuenta con un sistema de validacion funcional, desacoplado y claramente extensible.

Sus pilares actuales son:

- `Validator` como motor
- `RuleInterface` como contrato
- `ValidationRuleContext` como contexto de ejecucion
- `Rule` como API fluida y declarativa

La prioridad inmediata no es reescribirlo, sino documentarlo bien, estabilizar la arquitectura y seguir ampliando la expresividad sin perder claridad.

## 16. Documentos Relacionados

- `Docs/Validation/2-Architecture.md`
- `Docs/Validation/3-Roadmap.md`
- `Docs/Validation/6-Process.md`
- `Docs/Validation/7-Tests.md`
- `Docs/Validation/8-Use_Examples.md`
