# Validation Process

Subsistema: `Quantum\Validation`

Este documento define el proceso recomendado para trabajar sobre el sistema de validacion de VoltStack.

Su objetivo es que cualquier cambio en `Quantum\Validation` siga una ruta clara y repetible, reduciendo regresiones semanticas, duplicacion innecesaria y cambios mal clasificados.

## 1. Objetivo Del Proceso

El proceso de este subsistema busca asegurar cinco cosas:

1. que el cambio se implemente en la capa correcta
2. que el comportamiento observable se revise antes de merge
3. que la compatibilidad se evalúe de forma explícita
4. que la documentacion y pruebas acompañen a los cambios
5. que `Validator` no absorba complejidad innecesaria

## 2. Cuándo Usar Este Proceso

Este proceso aplica cuando se toca cualquiera de estas áreas:

- `Validator`
- `ValidatorInterface`
- `RuleInterface`
- `ValidationRuleContext`
- `ValidationException`
- `Rule`
- reglas en `Rules/`
- condiciones en `Conditions/`
- mensajes, placeholders o convenciones de error
- pruebas y documentación del subsistema

## 3. Regla Base

Antes de escribir código, hay que responder:

- el cambio es una regla nueva?
- es una corrección de semántica existente?
- es una mejora de ergonomía?
- es una integración con otro subsistema?
- es una refactorización interna?

La respuesta determina dónde debe vivir el cambio y qué validaciones adicionales son obligatorias.

## 4. Tipos De Cambio

### 4.1 Nueva Regla

Casos típicos:

- nueva regla atómica
- nueva regla semántica
- nueva regla dependiente
- nueva condición declarativa

Ruta preferida:

- crear clase dedicada
- exponerla vía `Rule` si aporta ergonomía pública
- añadir pruebas de comportamiento
- documentar uso si es API pública

### 4.2 Corrección De Comportamiento

Casos típicos:

- bug en mensajes
- bug en wildcards
- bug en placeholders
- bug en evaluación de condiciones

Ruta obligatoria:

- identificar el comportamiento actual
- demostrar que es incorrecto
- verificar si el cambio es breaking o no
- cubrir el caso con prueba específica
- reflejar el impacto en documentación si cambia semántica observable

### 4.3 Mejora De Ergonomía

Casos típicos:

- nuevo método en `Rule`
- builder más legible
- alias declarativo

Ruta recomendada:

- demostrar que mejora lectura real
- evitar duplicar semántica ya existente
- validar naming con la API actual
- tratarlo como cambio opt-in

### 4.4 Refactorización Interna

Casos típicos:

- extracción de helpers
- consolidación de lógica compartida
- reducción de duplicación

Ruta recomendada:

- no mezclar con cambios semánticos si puede evitarse
- verificar compatibilidad con reglas custom
- acompañar con tests de regresión si toca comportamiento delicado

### 4.5 Integración Externa

Casos típicos:

- uso desde HTTP
- integración con Actions
- integración con formularios o DTOs

Ruta recomendada:

- no forzar dependencias hacia dentro de `Quantum\Validation`
- mantener el subsistema desacoplado
- poner contratos de integración en la capa adecuada

## 5. Dónde Debe Vivir Cada Cambio

### 5.1 Cuando Crear Una Regla Objeto

Crear una regla objeto cuando:

- la regla tiene semántica propia
- necesita contexto
- necesita mensajes o placeholders propios
- puede reutilizarse en varios sitios

Esta es la ruta preferida para crecimiento.

### 5.2 Cuando Ampliar `Rule`

Ampliar `Rule` cuando:

- ya existe una regla objeto y falta ergonomía pública
- una firma fluida mejora claridad real
- la API nueva encaja con familias existentes

No ampliar `Rule` para añadir alias triviales sin valor claro.

### 5.3 Cuando Tocar `Validator`

Tocar `Validator` solo si el cambio realmente afecta:

- coordinación general
- normalización de reglas
- resolución de targets
- renderizado de mensajes
- flujo global de validación

Si la lógica puede vivir en una regla o helper compartido, no debe quedarse en `Validator`.

### 5.4 Cuando Crear Una Abstracción Interna

Crear una abstracción interna solo si:

- hay duplicación real en varias reglas
- la extracción mejora legibilidad
- la abstracción no complica más de lo que simplifica

## 6. Proceso Recomendado De Trabajo

La ruta recomendada para cualquier cambio es esta:

```txt
Understand change
    ↓
Classify change type
    ↓
Locate correct layer
    ↓
Assess compatibility impact
    ↓
Implement minimal solution
    ↓
Add or update tests
    ↓
Check diagnostics
    ↓
Review docs impact
    ↓
Merge
```

## 7. Paso 1 — Entender El Cambio

Antes de editar:

- identificar qué problema se resuelve
- identificar si afecta API pública o semántica
- identificar ejemplos reales de uso
- revisar código actual relacionado

Preguntas mínimas:

1. qué comportamiento existe hoy?
2. qué comportamiento se espera?
3. es bug, feature o refactor?
4. quién consume esta superficie?

## 8. Paso 2 — Clasificar El Impacto

Usar la guía de versionado para clasificar el cambio:

- patch
- minor
- major

Si el cambio toca:

- placeholders
- nombres de regla
- `empty`
- `accepted/declined`
- wildcards
- orden de evaluación

hay que asumir impacto alto hasta demostrar lo contrario.

## 9. Paso 3 — Elegir La Capa Correcta

Regla práctica:

- semántica de validación: regla objeto
- ergonomía pública: `Rule`
- condiciones composables: `Conditions/*`
- coordinación: `Validator`
- reutilización interna: abstract class o trait

Si una implementación obliga a meter demasiada lógica en `Validator`, probablemente la capa elegida es incorrecta.

## 10. Paso 4 — Implementar La Solución Más Pequeña Posible

La solución debe ser:

- localizada
- legible
- consistente con lo existente
- sin introducir abstractions innecesarias

Evitar en una misma entrega:

- cambio semántico
- refactor grande
- ampliación de API pública

si no es estrictamente necesario.

## 11. Paso 5 — Pruebas

Cada cambio sustantivo debe considerar pruebas.

### 11.1 Qué Probar

Como mínimo:

- caso válido
- caso inválido
- mensajes o placeholders si aplica
- wildcards si el cambio toca dependencias
- compatibilidad con API fluida si se añadió ergonomía

### 11.2 Qué No Hacer

Evitar pruebas de bajo valor que solo reescriben la implementación.

La prioridad está en:

- comportamiento observable
- regresiones reales
- escenarios con más riesgo semántico

### 11.3 Dónde Reforzar

Áreas sensibles:

- `requiredIf`, `requiredUnless`
- `acceptedIf`, `declinedIf`
- `prohibitedIf`
- `nullable`
- `same`, `confirmed`
- condiciones declarativas
- arrays anidados y wildcards

## 12. Paso 6 — Diagnósticos

Después de cambios sustantivos:

- revisar diagnósticos del archivo editado
- revisar especialmente tipos, imports y firmas

En documentación:

- comprobar que no haya errores formales
- releer consistencia con el resto de la serie documental

## 13. Paso 7 — Revisión De Documentación

No todos los cambios requieren un documento nuevo, pero sí una revisión documental.

### 13.1 Actualizar Docs Cuando

- se añade API pública
- cambia semántica observable
- cambia una convención recomendada
- aparece un patrón nuevo de uso

### 13.2 Documentos A Revisar

Según el cambio, revisar:

- `1-Validation_Context.md`
- `2-Architecture.md`
- `3-Roadmap.md`
- `5-Version_Guidelines.md`
- `7-Tests.md`
- `8-Use_Examples.md`

## 14. Proceso Específico Para Añadir Una Regla Nueva

Checklist recomendado:

1. definir si es atómica, semántica o dependiente
2. decidir si necesita clase dedicada
3. decidir si necesita factory en `Rule`
4. definir mensajes y placeholders
5. evaluar impacto en wildcards
6. añadir pruebas
7. documentar ejemplos si es pública

## 15. Proceso Específico Para Tocar `Validator`

Antes de tocar `Validator`, responder:

1. esto no puede vivir en una regla?
2. esto no puede vivir en un helper compartido?
3. esto afecta coordinación general?
4. el cambio altera flujo observable?

Si la respuesta a las dos primeras preguntas es "sí, puede vivir fuera", entonces no debe tocarse `Validator`.

Si se toca:

- mantener el cambio lo más pequeño posible
- cubrir con pruebas de flujo
- revisar compatibilidad con reglas string y objeto

## 16. Proceso Específico Para Cambios En La DSL

Antes de ampliar la DSL declarativa:

- confirmar que existe caso de uso real
- revisar si ya hay una forma clara de expresarlo
- evitar superposición de nombres
- mantener separación entre condición y acción

Checklist:

1. la semántica es clara?
2. el naming encaja con `when`, `is`, `in`, `exists`, `allOf`, etc.?
3. es reutilizable?
4. requiere placeholders o labels nuevos?
5. afecta reglas existentes?

## 17. Proceso Específico Para Breaking Changes

Si un cambio potencialmente rompe compatibilidad:

1. documentar por qué rompe
2. clasificarlo como major salvo justificación sólida
3. preparar ruta de migración
4. actualizar guía de versionado si aplica
5. reflejarlo en documentación y ejemplos

No deben entrar breaking changes semánticos como "ajustes menores" sin nota explícita.

## 18. Checklist Antes De Merge

Antes de cerrar un cambio en `Quantum\Validation`, revisar:

- el cambio está en la capa correcta
- la compatibilidad fue evaluada
- los tests relevantes existen o fueron actualizados
- los diagnósticos están limpios
- la documentación afectada fue revisada
- el cambio no añade azúcar innecesario
- `Validator` no absorbió lógica evitable

## 19. Señales De Mala Dirección

Hay señales que indican que el cambio va por mal camino:

- la lógica termina en `Validator` por comodidad
- aparece un alias nuevo sin caso claro
- se rompe semántica existente sin reconocerlo
- se cambia un mensaje o placeholder sin medir impacto
- se añade una abstracción solo para "ordenar" sin reducir complejidad real

Cuando aparezca una de estas señales, conviene parar y replantear el enfoque.

## 20. Tesis Operativa

Trabajar bien en `Quantum\Validation` no consiste en añadir reglas rápidamente.

Consiste en:

- entender el cambio
- ubicarlo bien
- proteger semántica
- validar compatibilidad
- dejar pruebas y docs a la altura

Ese proceso es el que mantiene el subsistema pequeño, confiable y extensible.

## 21. Documentos Relacionados

- `Docs/Validation/1-Validation_Context.md`
- `Docs/Validation/2-Architecture.md`
- `Docs/Validation/3-Roadmap.md`
- `Docs/Validation/5-Version_Guidelines.md`
- `Docs/Validation/7-Tests.md`
- `Docs/Validation/8-Use_Examples.md`
