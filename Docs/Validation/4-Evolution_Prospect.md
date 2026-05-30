# Validation Evolution Prospect

Subsistema: `Quantum\Validation`

Este documento describe la proyeccion evolutiva del sistema de validacion de VoltStack.

A diferencia del roadmap, que ordena entregables por fases, este archivo explora en que puede convertirse `Quantum\Validation` si mantiene una evolucion coherente con la arquitectura del framework.

## 1. Proposito De Esta Prospectiva

El objetivo de esta prospectiva es responder a preguntas de direccion:

- que rol puede ocupar validacion dentro del ecosistema VoltStack
- que capacidades emergen si el subsistema madura correctamente
- que limites conviene preservar para no romper su simplicidad
- que forma podria tomar una validacion realmente backend-first y framework-wide

No es un compromiso cerrado de implementacion.

Es una vision razonada de evolucion compatible con:

- `1-Validation_Context.md`
- `2-Architecture.md`
- `3-Roadmap.md`

## 2. Hipotesis Central

La hipotesis de evolucion es esta:

`Quantum\Validation` no deberia quedarse como un validador de arrays.

Deberia convertirse progresivamente en la capa comun de semantica de entrada del framework.

Eso significa que, a medio plazo, la validacion puede actuar como:

- lenguaje de contratos de entrada
- sistema compartido de errores de input
- base para formularios, request objects y DTOs
- capa de consistencia entre HTTP, Actions y componentes
- punto de integracion entre datos crudos, reglas y datos confiables

## 3. Evolucion De Rol

### 3.1 Rol Actual

Hoy el subsistema es principalmente:

- un motor de validacion
- una API fluida para reglas
- una DSL declarativa en crecimiento
- una utilidad desacoplada de capa de aplicacion

### 3.2 Rol Esperado A Medio Plazo

A medio plazo deberia convertirse en:

- una infraestructura de contratos de entrada
- una dependencia transversal para otros paquetes
- una base reutilizable para validacion declarativa por clase
- una fuente comun de mensajes y errores de entrada

### 3.3 Rol Esperado A Largo Plazo

A largo plazo podria convertirse en:

- un lenguaje interno estable de constraints
- una pieza base para runtime forms y componentes server-driven
- una capa reusable para serializacion, mapeo y coercion controlada
- un sistema observable y diagnosticable dentro del ecosistema

## 4. De Validador A Sistema De Contratos

La evolucion mas importante no es añadir muchas reglas nuevas.

Es pasar de:

```txt
array + rules -> errors
```

a algo conceptualmente mas rico:

```txt
input contract -> normalization -> validation -> trusted payload -> domain handoff
```

En esa vision, la validacion deja de ser solo una puerta de rechazo.

Pasa a ser la fase donde el framework:

- entiende la estructura del input
- decide que datos son confiables
- prepara datos para la siguiente capa

## 5. Perspectiva De Madurez

La madurez del subsistema puede imaginarse en cuatro niveles.

### 5.1 Nivel 1: Validation Engine

Capacidad principal:

- validar arrays con reglas y devolver errores

Es el nivel donde el sistema ya esta hoy.

### 5.2 Nivel 2: Semantic Validation Layer

Capacidad principal:

- expresar reglas con lenguaje de mayor intencion

Aqui encajan:

- `Rule`
- reglas semanticas
- reglas dependientes
- DSL declarativa

El subsistema tambien se encuentra parcialmente en este nivel.

### 5.3 Nivel 3: Input Contract Layer

Capacidad principal:

- modelar entradas validables como contratos reutilizables

Escenarios posibles:

- request classes con reglas propias
- DTOs validados
- formularios declarativos
- contratos de Action

Este nivel aun no esta formalizado, pero parece la siguiente evolucion natural.

### 5.4 Nivel 4: Framework-Wide Input Semantics

Capacidad principal:

- compartir semantica de input en todo VoltStack

En este nivel la validacion se convierte en infraestructura comun para:

- HTTP
- Actions
- componentes
- modulos
- herramientas de DX

## 6. Posibles Direcciones De Evolucion

### 6.1 Input Objects

Una linea fuerte de evolucion es el soporte a objetos de entrada.

Ejemplos de posibilidades futuras:

- `FormRequest` o equivalente desacoplado
- DTOs con reglas propias
- input schemas definidos por clase

La validacion, en ese escenario, no reemplaza al objeto de entrada.

Lo habilita.

### 6.2 Data Normalization Y Coercion

Otra linea plausible es introducir una capa ligera de normalizacion controlada.

No se trata de convertir validacion en un sistema de casting masivo.

Se trata de permitir, con reglas claras:

- normalizacion de strings
- saneado basico
- coerciones seguras y explicitas
- preparacion previa al handoff de dominio

Esto solo deberia existir si puede hacerse sin confundir validacion con transformacion arbitraria.

### 6.3 Declarative Contracts

La DSL declarativa actual podria evolucionar hacia contratos mas completos.

Ejemplos conceptuales:

- condiciones reutilizables compartidas por varias reglas
- grupos de reglas con semantica de negocio
- constraints nombrados y componibles

La clave seria mantener una frontera clara:

- las condiciones modelan semantica
- las reglas aplican esa semantica

### 6.4 Error Semantics

La gestion de errores puede madurar mucho mas.

A futuro, `Quantum\Validation` podria ofrecer:

- codigos de error mas estables
- metadatos estructurados por error
- mejor diferenciacion entre fallo semantico y fallo de formato
- payloads de error mas utiles para APIs y runtimes reactivos

Esto es especialmente relevante si VoltStack evoluciona hacia componentes y SPAs mas ricos.

## 7. Validacion Como Infraestructura Transversal

Si el framework madura en la direccion prevista, la validacion puede convertirse en una pieza comun para varios subsistemas.

### 7.1 HTTP

Vision futura:

- reglas declarativas por endpoint
- request validation desacoplada del controller
- respuestas 422 consistentes y estandarizadas

### 7.2 Actions

Vision futura:

- contratos de entrada antes de `handle()`
- separacion clara entre input validation y business logic
- Actions autocontenidas con semantica de entrada visible

### 7.3 Components

Vision futura:

- validacion de props
- validacion de state server-driven
- errores consistentes para interacciones incrementales

### 7.4 Modules

Vision futura:

- reglas compartidas entre modulos
- contratos de entrada versionables
- menor duplicacion de semantica entre bounded contexts

## 8. Futuro De La API Fluida

La API `Rule` ya es una pieza central del subsistema.

Su futuro razonable no es crecer sin limite, sino consolidarse como una fachada semantica estable.

### 8.1 Lo Que Si Puede Ocurrir

- familias coherentes de reglas
- builders mejor definidos
- condiciones declarativas mas reutilizables
- mejor legibilidad en escenarios complejos

### 8.2 Lo Que Conviene Evitar

- aliases redundantes
- multiples nombres para la misma semantica
- sobrecarga de metodos sin una convencion clara
- una DSL tan amplia que termine siendo mas dificil de aprender que las reglas string

### 8.3 Prospecto Mas Deseable

El mejor escenario es que `Rule` se perciba como:

- pequeno
- consistente
- semantico
- composable

y no como una lista infinita de helpers.

## 9. Futuro De La DSL Declarativa

La DSL declarativa es una de las piezas con mas potencial.

Bien dirigida, puede convertirse en el puente entre reglas tecnicas y semantica de negocio.

### 9.1 Potencial Real

Puede servir para:

- expresar condiciones de forma legible
- reutilizar semantica entre reglas
- encapsular variaciones de negocio sin tocar `Validator`

### 9.2 Direccion Sana

La DSL deberia avanzar hacia:

- composicion clara
- nombres previsibles
- interoperabilidad con reglas existentes
- objetos de condicion reutilizables

### 9.3 Direccion Peligrosa

La DSL seria un problema si intenta convertirse en:

- lenguaje total de validacion
- reemplazo de todas las reglas
- mini framework de expresiones dificil de mantener

Su valor esta en complementar el sistema, no en sustituirlo por completo.

## 10. Futuro De La Arquitectura Interna

La arquitectura interna tambien tiene una proyeccion clara.

### 10.1 `Validator` Mas Delgado

A medio plazo es razonable esperar:

- menos logica semantica inline
- mas comportamiento en reglas objeto
- una mejor separacion entre coordinacion y evaluacion

### 10.2 Abstracciones Compartidas Mas Claras

Es probable que aparezcan nuevas bases internas si realmente reducen duplicacion.

Pero la señal de calidad no sera tener muchas abstracciones.

Sera tener pocas, utiles y estables.

### 10.3 Convenciones Mas Fuertes

La arquitectura podria beneficiarse de convenciones mas explicitas sobre:

- placeholders
- nombres de regla
- resolucion de dependencias
- compatibilidad con wildcards
- contratos de extensibilidad

## 11. Posible Evolucion De Tipado Y Datos Confiables

Una evolucion especialmente interesante es la separacion entre:

- datos de entrada crudos
- datos validados
- datos listos para dominio

Si VoltStack avanza hacia DTOs y contratos tipados, `Quantum\Validation` podria ser la puerta de entrada de ese pipeline.

No necesariamente generando objetos automaticamente, pero si dejando una semantica clara de:

- que entra
- que se acepta
- que se rechaza
- que puede pasar al dominio

## 12. Observabilidad Y DX

En un framework que aspira a DX moderna, la validacion no deberia ser una caja negra.

A futuro seria deseable disponer de:

- mejores mensajes de desarrollo
- ejemplos oficiales mas ricos
- inspeccion mas clara de errores
- diagnosticos de reglas complejas
- tooling que ayude a entender fallos de wildcards y dependencias

Esto no implica telemetria pesada en el subsistema.

Implica hacerlo mas explicable.

## 13. Limites Que Conviene Preservar

La evolucion del subsistema solo sera sana si conserva ciertos limites.

### 13.1 No Convertirlo En ORM Validation

La validacion no debe depender de persistencia o modelos.

### 13.2 No Convertirlo En Lenguaje Gigante

La API publica no debe crecer hasta ser mas compleja que el problema que resuelve.

### 13.3 No Mezclar Todo Con Todo

Validacion, transformacion, autorizacion y logica de negocio deben seguir separadas.

Puede haber integraciones.

No debe haber fusion conceptual.

### 13.4 No Romper El Small Core

El mayor activo del subsistema hoy es que se puede entender.

Ese atributo debe protegerse.

## 14. Escenario De Mejor Caso

El mejor escenario realista para `Quantum\Validation` seria este:

- `Validator` sigue siendo pequeno y estable
- la mayoria de la complejidad vive en reglas y contratos claros
- la API `Rule` es semantica y consistente
- la DSL declarativa resuelve condiciones complejas sin volverse caotica
- HTTP, Actions y componentes reutilizan la misma semantica de input
- la documentacion y los tests permiten evolucionar sin miedo

## 15. Escenario De Riesgo

El peor escenario razonable seria:

- `Validator` crece sin control
- la API fluida se llena de aliases superpuestos
- la DSL declarativa intenta resolver todo
- las reglas dependientes divergen en comportamiento
- los mensajes dejan de ser previsibles
- cada integracion reimplementa su propia capa de validacion

Esta prospectiva sirve precisamente para evitar ese camino.

## 16. Tesis Final

Si evoluciona bien, `Quantum\Validation` puede convertirse en algo mas valioso que un simple motor de validacion.

Puede convertirse en la capa comun de semantica de entrada de VoltStack.

Eso lo volveria importante no por la cantidad de reglas que tenga, sino por la claridad con la que conecta:

- input
- reglas
- errores
- contratos
- handoff hacia la logica de aplicacion

## 17. Resumen

La mejor evolucion posible del subsistema es:

- mas semantica
- mas reusable
- mas integrable
- mas explicable

pero no necesariamente mas grande.

Ese equilibrio es el centro de esta prospectiva.

## 18. Documentos Relacionados

- `Docs/Validation/1-Validation_Context.md`
- `Docs/Validation/2-Architecture.md`
- `Docs/Validation/3-Roadmap.md`
- `Docs/Validation/6-Process.md`
- `Docs/Validation/7-Tests.md`
- `Docs/Validation/8-Use_Examples.md`
