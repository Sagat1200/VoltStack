# Validation Roadmap

Subsistema: `Quantum\Validation`

Este documento define la hoja de ruta del sistema de validacion de VoltStack.

Su objetivo no es listar deseos abstractos, sino ordenar la evolucion real del subsistema desde su estado actual hasta una version estable, extensible e integrada con el resto del framework.

## 1. Punto De Partida

Hoy `Quantum\Validation` ya dispone de una base funcional:

- `Validator` operativo
- soporte dual para reglas string y reglas objeto
- `ValidationRuleContext`
- `ValidationException`
- API fluida `Rule`
- reglas semanticas y dependientes
- DSL declarativa con composicion basica
- soporte para wildcards y mensajes personalizados

Esto significa que el roadmap no parte desde cero.

La siguiente etapa no es construir un MVP, sino consolidar, completar y preparar el subsistema para integraciones futuras.

## 2. Objetivos Del Roadmap

La evolucion del subsistema debe perseguir estos objetivos:

1. estabilizar el comportamiento actual
2. completar familias de reglas faltantes
3. reducir deuda estructural dentro de `Validator`
4. fortalecer la extensibilidad publica
5. integrar validacion con otros paquetes del framework
6. mejorar la documentacion, pruebas y politica de versionado

## 3. Principios De Evolucion

El roadmap de validacion sigue estos principios:

### 3.1 Small Core First

La prioridad no es convertir `Validator` en un motor complejo, sino preservar un nucleo pequeno y mover la complejidad donde aporte mas claridad:

- reglas objeto
- abstracciones internas reutilizables
- condiciones declarativas
- integraciones de capa superior

### 3.2 API Stability Before Expansion

Antes de abrir demasiadas nuevas superficies publicas, hay que estabilizar:

- naming
- convenciones de mensajes
- placeholders
- comportamiento con wildcards
- semantica de reglas dependientes

### 3.3 Real Integration Over Cosmetic Sugar

La ergonomia fluida es importante, pero no debe crecer desconectada del resto del framework.

La hoja de ruta prioriza integraciones reales con:

- HTTP
- Actions
- formularios o request objects
- componentes server-driven

### 3.4 Tests And Docs As Product Work

En este subsistema, pruebas y documentacion no son extras.

Son parte directa de la entrega, porque:

- el comportamiento es muy semantico
- pequeños cambios pueden romper mensajes o flujos
- la API publica debe ser predecible

## 4. Estado Actual Resumido

Resumen de situacion:

| Area | Estado actual |
| --- | --- |
| Motor base | funcional |
| Reglas string | funcionales pero concentradas en `Validator` |
| Reglas objeto | funcionales y en expansion |
| Reglas dependientes | presentes y reutilizando bases comunes |
| DSL declarativa | operativa y ya composable |
| Integracion externa | minima |
| Politica de extension publica | parcial |
| Documentacion | en construccion |

## 5. Fases Del Roadmap

La evolucion recomendada se divide en seis fases.

```txt
Current Functional Base
    ↓
Phase 1 Stabilization
    ↓
Phase 2 Rule Coverage
    ↓
Phase 3 Public Extensibility
    ↓
Phase 4 Framework Integration
    ↓
Phase 5 Validation Ergonomics
    ↓
Phase 6 Hardening And Release Discipline
```

## 6. Phase 1 — Stabilization

### 6.1 Objetivo

Consolidar el comportamiento actual del subsistema antes de expandirlo demasiado.

### 6.2 Enfoque

- cerrar documentacion base
- revisar consistencia de mensajes
- revisar convenciones de placeholders
- asegurar comportamiento uniforme con wildcards
- reforzar cobertura de tests para regresiones reales

### 6.3 Entregables

- contexto y arquitectura documentados
- roadmap y proceso documentados
- convenciones claras para reglas dependientes
- cobertura minima de regresion para la API `Rule`
- checklist de compatibilidad entre reglas string y objeto

### 6.4 Criterios De Salida

La fase se considera completa cuando:

- el comportamiento actual queda documentado
- las reglas nuevas recientes tienen cobertura razonable
- no hay dudas semanticas fuertes sobre `accepted`, `declined`, `nullable`, `same`, `confirmed`, `requiredIf` y la DSL `when(...)`

## 7. Phase 2 — Rule Coverage

### 7.1 Objetivo

Completar familias de reglas utiles y cerrar huecos funcionales evidentes.

### 7.2 Prioridades

Familia `required/prohibited`:

- `requiredWithAll`
- `requiredWithoutAll`
- `prohibitedUnless`
- `prohibits`

Familia de aceptacion y rechazo:

- `acceptedIf` y `declinedIf` estabilizados
- posibles variantes expresivas o alias si realmente aportan claridad

Familia de comparacion:

- posibles reglas de desigualdad o inclusion semantica
- reglas adicionales reutilizando la DSL declarativa

Familia de presencia:

- consolidar `present`, `nullable`, `prohibited`
- evaluar `filled` como regla directa si aporta valor real

### 7.3 Reglas Para Priorizar

Prioridad alta:

- cerrar familias de dependencias ya empezadas
- mejorar mensajes de reglas compuestas
- asegurar soporte consistente para arrays anidados

Prioridad media:

- nuevas reglas de comparacion
- variantes menos frecuentes

Prioridad baja:

- azucar sintactico sin mejora real de semantica

### 7.4 Criterios De Salida

- las familias principales de reglas dependientes quedan coherentes
- la API `Rule` cubre los casos de negocio mas frecuentes
- los ejemplos documentados ya no dependen de workarounds

## 8. Phase 3 — Public Extensibility

### 8.1 Objetivo

Definir una historia clara para extender el subsistema sin tocar el core.

### 8.2 Problema Actual

Hoy es posible crear reglas objeto, pero todavia falta una politica publica mas formal para:

- registro de reglas reutilizables
- ampliacion de mensajes
- integracion de convenciones comunes
- documentacion de extensibilidad

### 8.3 Entregables

- guia oficial para crear reglas custom
- convencion recomendada para placeholders y labels
- posible registro o factory publica para reglas extensibles
- patrones oficiales para callbacks frente a reglas dedicadas

### 8.4 Meta Arquitectonica

El objetivo es que un desarrollador pueda extender validacion mediante:

- una clase de regla
- una condicion declarativa
- una integracion de alto nivel

sin tener que modificar `Validator`.

## 9. Phase 4 — Framework Integration

### 9.1 Objetivo

Integrar `Quantum\Validation` con otros subsistemas de VoltStack.

### 9.2 Integraciones Objetivo

HTTP:

- request validation
- validacion antes de controller/action
- respuestas consistentes para errores 422

Actions:

- validacion declarativa de input
- hooks o contratos previos a `handle()`

Form objects o DTOs:

- clases con reglas propias
- mapeo tipado de datos validados

Componentes server-driven:

- validacion de props o state
- errores de entrada consistentes

### 9.3 Resultado Esperado

La validacion deja de ser solo un paquete utilitario y pasa a ser una pieza transversal del framework.

## 10. Phase 5 — Validation Ergonomics

### 10.1 Objetivo

Refinar la ergonomia publica del sistema una vez que el comportamiento base y las integraciones ya estan mas estables.

### 10.2 Lineas De Trabajo

- mejorar legibilidad de la DSL declarativa
- decidir si merece ampliar combinadores
- evaluar builders mas expresivos para escenarios complejos
- documentar patrones recomendados frente a patrones desaconsejados

### 10.3 Lo Que No Debe Pasar

Esta fase no debe degenerar en una expansion infinita de aliases.

Toda nueva API fluida debe justificar:

- mejora real de legibilidad
- coherencia con lo existente
- bajo coste de mantenimiento

## 11. Phase 6 — Hardening And Release Discipline

### 11.1 Objetivo

Preparar el subsistema para estabilidad de version y adopcion mas amplia dentro del framework.

### 11.2 Trabajo Necesario

- definir politica de cambios compatibles
- identificar puntos sensibles a breaking changes
- reforzar pruebas de regresion
- consolidar documentacion de ejemplos
- documentar limites conocidos y decisiones de diseño

### 11.3 Resultado Esperado

`Quantum\Validation` queda listo para:

- usarse como dependencia confiable de otros paquetes
- evolucionar con versionado semantico
- soportar nuevas integraciones sin reabrir el core en cada iteracion

## 12. Backlog Tecnico Recomendado

Este backlog no esta estrictamente secuenciado, pero conviene mantenerlo visible.

### 12.1 Backlog Del Motor

- reducir logica residual de reglas string dentro de `Validator`
- documentar mejor ramas internas de evaluacion
- identificar posibles extracciones seguras sin perder simplicidad

### 12.2 Backlog De Mensajes

- unificar criterios de placeholders
- formalizar estrategia de i18n
- documentar diferencias entre `:value` y `:values`

### 12.3 Backlog De Wildcards

- ampliar casos con arrays anidados complejos
- reforzar pruebas de campos dependientes resueltos
- documentar patrones validos y limites actuales

### 12.4 Backlog De DSL

- evaluar nuevos combinadores solo si hay casos reales
- mantener separacion entre condicion y accion
- evitar duplicar capacidades ya cubiertas por reglas dedicadas

## 13. Riesgos Del Roadmap

### 13.1 Riesgo De Azucar Excesivo

La API fluida puede crecer demasiado rapido y fragmentar la semantica.

Mitigacion:

- priorizar familias coherentes
- exigir ejemplos reales de uso

### 13.2 Riesgo De Centralizacion En `Validator`

Si cada nueva regla string entra al motor, el archivo tendera a volverse mas fragil.

Mitigacion:

- favorecer reglas objeto
- reutilizar bases abstractas

### 13.3 Riesgo De Integracion Prematura

Integrar con HTTP o formularios sin contratos estables puede forzar cambios publicos luego.

Mitigacion:

- estabilizar primero contexto, mensajes y convenciones

## 14. Prioridades Inmediatas

Lo siguiente recomendado, en orden, es:

1. completar esta serie documental
2. cerrar `Process`, `Tests` y `Use_Examples`
3. revisar backlog real de reglas dependientes que faltan
4. decidir politica minima de extensibilidad publica
5. preparar la primera integracion formal con HTTP o Actions

## 15. Criterios De Exito

El roadmap de validacion se considera bien ejecutado cuando:

- la API `Rule` es expresiva sin ser caotica
- el motor sigue siendo mantenible
- las reglas dependientes son coherentes y previsibles
- las integraciones con otros paquetes no duplican logica
- la documentacion permite entender el subsistema sin leer todo el codigo

## 16. Resumen

La evolucion correcta de `Quantum\Validation` no consiste en añadir reglas sin fin.

Consiste en avanzar en este orden:

- estabilizar
- completar
- abrir extension
- integrar
- endurecer

Ese orden protege tanto la simplicidad del core como la calidad de la API publica.

## 17. Documentos Relacionados

- `Docs/Validation/1-Validation_Context.md`
- `Docs/Validation/2-Architecture.md`
- `Docs/Validation/6-Process.md`
- `Docs/Validation/7-Tests.md`
- `Docs/Validation/8-Use_Examples.md`
