# Validation Tests

Subsistema: `Quantum\Validation`

Este documento define la estrategia de pruebas del sistema de validacion de VoltStack.

Su objetivo es que el subsistema evolucione con confianza, sin depender de pruebas casuales ni de cobertura superficial que no proteja el comportamiento realmente importante.

## 1. Objetivo De Las Pruebas

Las pruebas de `Quantum\Validation` deben proteger principalmente:

- comportamiento observable
- semantica de reglas
- mensajes y placeholders
- flujo de evaluacion
- compatibilidad entre reglas string y reglas objeto
- resolucion de campos anidados y wildcards

La meta no es maximizar un porcentaje abstracto de cobertura.

La meta es reducir riesgo de regresiones semanticas.

## 2. Tesis De Pruebas

En este subsistema, una buena prueba no verifica implementacion interna.

Verifica contratos observables.

Eso significa que las pruebas deben centrarse en:

- que valida
- que falla
- que error produce
- como se comporta el flujo
- como se resuelven dependencias

Y no en:

- detalles privados de implementacion
- estructura accidental del codigo
- helpers internos sin impacto externo directo

## 3. Principios De La Estrategia

### 3.1 Behavior First

La prioridad es probar lo que experimenta el consumidor:

- payload validado
- errores devueltos
- mensajes finales
- comportamiento de callbacks y cortes de flujo

### 3.2 Semantics Over Structure

Si una regla cambia su significado, el daño puede ser mayor que un cambio de firma.

Por eso las pruebas deben proteger semantica, no solo existencia de clases o metodos.

### 3.3 High-Risk Areas Deserve More Depth

No todas las areas requieren la misma densidad de pruebas.

Las zonas con más riesgo son:

- reglas dependientes
- placeholders
- wildcards
- DSL declarativa
- cortes de flujo
- equivalencia entre reglas string y reglas objeto

### 3.4 Small Useful Tests

Conviene preferir pruebas:

- pequeñas
- legibles
- enfocadas en una semantica concreta

frente a mega-pruebas que cubren demasiadas cosas y dificultan diagnosticar fallos.

## 4. Que Deben Proteger Las Pruebas

### 4.1 Reglas String

Las pruebas deben garantizar que las reglas string legacy siguen siendo fiables y compatibles.

### 4.2 Reglas Objeto

Las pruebas deben garantizar que las reglas objeto:

- validan correctamente
- producen nombres de regla esperados
- respetan placeholders
- conviven con reglas string

### 4.3 API Fluida `Rule`

Las pruebas deben cubrir:

- factories publicos
- argumentos expresivos
- mensajes esperados
- consistencia con el motor

### 4.4 DSL Declarativa

Las pruebas deben cubrir:

- condiciones por valor
- condiciones por estado
- composicion
- chaining
- derivacion en reglas condicionales

### 4.5 Flujo Del Motor

Las pruebas deben proteger:

- `bail`
- `stopOnFirstFailure()`
- `skipRemainingRules()`
- callbacks `after()`
- mezcla de reglas string y objeto

## 5. Niveles De Prueba Recomendados

### 5.1 Pruebas De Integracion Del Subsistema

Son la capa principal.

Normalmente validan:

- `Validator`
- `ValidationException`
- mensajes finales
- interacción entre reglas, contexto y renderizado

Hoy el archivo principal de esta capa es [ValidatorTest.php](file:///c:/W4/Packages/VoltStack3/app-skeleton/vendor/voltstack/framework/tests/Validation/ValidatorTest.php).

### 5.2 Pruebas Focalizadas De Reglas

Cuando una regla tiene semantica delicada o mucha logica propia, puede merecer pruebas mas focalizadas.

Ejemplos:

- reglas dependientes complejas
- reglas con wildcards
- reglas con placeholders especializados

Estas pruebas solo aportan valor si reducen ambigüedad real.

### 5.3 Pruebas De Regresion

Deben aparecer cuando:

- se corrige un bug
- se descubre una combinación frágil
- se toca una zona con historial de problemas

La regla es simple:

cada bug importante debe intentar dejar una prueba que impida su reaparición.

## 6. Lo Que Ya Está Cubierto Hoy

Según el estado actual de [ValidatorTest.php](file:///c:/W4/Packages/VoltStack3/app-skeleton/vendor/voltstack/framework/tests/Validation/ValidatorTest.php), el subsistema ya cubre una parte significativa del comportamiento.

Areas actualmente cubiertas:

- payloads válidos e inválidos
- mensajes personalizados y aliases de atributos
- `bail`
- `stopOnFirstFailure()`
- callbacks `after()`
- reglas string clásicas
- reglas objeto
- mezcla de reglas string y objeto
- reglas semánticas
- reglas dependientes
- campos anidados y wildcards
- placeholders en escenarios complejos
- API fluida `Rule`
- DSL declarativa con composición

Esto es una buena base, pero no elimina la necesidad de seguir añadiendo pruebas orientadas al riesgo.

## 7. Zonas De Riesgo Alto

Las siguientes áreas merecen especial atención.

### 7.1 Placeholders

Riesgos:

- perder `:other`
- perder `:value` o `:values`
- renderizar listas de valores con formato inesperado
- romper labels personalizados

### 7.2 Wildcards Y Dependencias

Riesgos:

- resolver mal `items.*.field`
- mezclar targets de un item con otro
- fallar en `same`, `confirmed` o condiciones dependientes

### 7.3 `nullable`

Riesgos:

- cortar reglas cuando no debe
- no cortar reglas cuando sí debe
- divergir entre versión string y objeto

### 7.4 `requiredIf`, `requiredUnless`, `acceptedIf`, `declinedIf`, `prohibitedIf`

Riesgos:

- semántica inconsistente según tipo de condición
- diferencias entre `bool`, `Closure`, string y condición declarativa
- mensajes incompatibles con placeholders

### 7.5 DSL Declarativa

Riesgos:

- composición con semántica confusa
- diferencias entre `is`, `in`, `exists`, `empty` y sus derivados `then...`
- pérdida de compatibilidad al ampliar builders

### 7.6 Flujo Global

Riesgos:

- cambios en orden de evaluación
- cambios en `bail`
- cambios en `after()`
- efectos secundarios persistentes entre validaciones

## 8. Cobertura Minima Segun Tipo De Cambio

Esta es la cobertura mínima recomendada.

### 8.1 Nueva Regla Pública

Como mínimo:

- caso válido
- caso inválido
- mensaje por defecto si aplica
- mensaje personalizado si aplica
- integración desde `Rule` si tiene factory fluido

### 8.2 Nueva Regla Dependiente

Como mínimo:

- caso válido
- caso inválido
- placeholders `:other`, `:value` o `:values`
- resolución correcta de dependencia
- caso con wildcard si la regla soporta campos dependientes

### 8.3 Nueva Condición Declarativa

Como mínimo:

- condición verdadera
- condición falsa
- uso directa en `...If(...)`
- uso derivado con `thenRequired()` o equivalente
- composición si interactúa con `allOf(...)` o `anyOf(...)`

### 8.4 Cambio En `Validator`

Como mínimo:

- caso positivo del flujo afectado
- caso negativo del flujo afectado
- compatibilidad con reglas string
- compatibilidad con reglas objeto
- prueba de regresión del motivo que obligó al cambio

### 8.5 Cambio En Mensajes O Placeholders

Como mínimo:

- mensaje por defecto
- mensaje personalizado
- atributos personalizados
- placeholders afectados en contexto real

## 9. Matriz Practica De Casos

Para cada regla o comportamiento importante, conviene pensar en esta matriz:

| Caso | Debe probarse |
| --- | --- |
| payload válido | sí |
| payload inválido | sí |
| mensaje por defecto | cuando aplique |
| mensaje custom | cuando aplique |
| alias de atributo | cuando aplique |
| wildcard | si hay dependencias o arrays |
| condición booleana | si la regla la acepta |
| condición por callback | si la regla la acepta |
| condición declarativa | si la regla la acepta |

## 10. Qué Hace Una Prueba De Alto Valor

Una prueba de alto valor en este subsistema suele tener estas características:

- nombre claro
- payload pequeño
- reglas mínimas necesarias
- una semántica concreta
- assertions sobre resultado observable

Ejemplo de buen enfoque:

- una prueba que verifica que `requiredIf` con wildcard resuelve el campo correcto

Ejemplo de mal enfoque:

- una prueba enorme que mezcla diez reglas distintas y falla con un mensaje ambiguo

## 11. Qué Evitar

### 11.1 Pruebas Espejo De Implementacion

No aporta mucho valor probar helpers internos solo porque existen, si ya quedan protegidos por pruebas de integración más representativas.

### 11.2 Pruebas Gigantes Sin Foco

Aunque las pruebas amplias pueden ser útiles como smoke tests, no deben sustituir pruebas específicas de semántica.

### 11.3 Repetición Innecesaria

No hace falta probar exactamente la misma semántica en cinco sitios si una prueba ya cubre ese contrato de forma clara.

### 11.4 Assertions Débiles

Evitar assertions del tipo:

- "no lanzó excepción"
- "hay errores"

cuando el valor real está en verificar:

- qué error
- en qué campo
- con qué mensaje

## 12. Convenciones Recomendadas

### 12.1 Nombres De Test

Preferir nombres que describan:

- qué se valida
- en qué contexto
- qué resultado se espera

El patrón actual `test_validator_supports_...` y `test_validator_throws_errors_for_...` es aceptable y consistente.

### 12.2 Payloads

Usar payloads:

- pequeños
- explícitos
- cercanos al caso real

### 12.3 Mensajes

Cuando el cambio afecta placeholders o rendering, conviene afirmar el mensaje exacto.

Cuando no afecta mensajes, no hace falta sobreespecificar más de lo necesario.

## 13. Estrategia Para Bugs

Cuando aparece un bug en validación, la secuencia recomendada es:

1. reproducirlo en test
2. verificar el comportamiento actual roto
3. aplicar la corrección mínima
4. dejar el test como regresión permanente

Si el bug afecta semántica observable, conviene además revisar:

- `5-Version_Guidelines.md`
- `6-Process.md`

## 14. Estrategia Para Refactors

Un refactor debe venir acompañado de pruebas si toca:

- `Validator`
- resolución de wildcards
- placeholders
- condiciones declarativas
- abstracciones base reutilizadas por varias reglas

La intención no es probar el refactor en sí.

Es probar que el comportamiento no cambió.

## 15. Estrategia Para Breaking Changes

Si se hace un cambio potencialmente breaking, las pruebas deben reflejarlo explícitamente.

Conviene tener:

- pruebas que muestren el nuevo comportamiento
- notas de migración
- documentación alineada

Si es posible, también ayuda mantener ejemplos comparables entre comportamiento anterior y nuevo durante la transición documental.

## 16. Huecos Recomendados A Futuro

Aunque la cobertura actual es sólida, todavía merece la pena reforzar:

- casos más finos de wildcards en composición declarativa
- pruebas más focalizadas para listas de placeholders `:values`
- escenarios frontera de `empty()` y `filled()`
- escenarios frontera de `nullable()` frente a otras reglas
- más regresiones si aparecen integraciones con HTTP o DTOs

## 17. Criterios De Suficiencia

Un cambio en `Quantum\Validation` puede considerarse suficientemente probado cuando:

- cubre el comportamiento principal
- cubre el fallo principal
- cubre los placeholders o mensajes si son parte del cambio
- cubre wildcards o dependencias si el cambio los toca
- deja el subsistema con menos riesgo que antes, no con más

## 18. Tesis Final

La estrategia correcta de pruebas para `Quantum\Validation` no es perseguir volumen.

Es perseguir confianza.

Eso implica:

- pruebas pequeñas
- foco en semántica
- atención especial a wildcards, placeholders y flujo
- regresiones permanentes para bugs reales

## 19. Documentos Relacionados

- `Docs/Validation/2-Architecture.md`
- `Docs/Validation/5-Version_Guidelines.md`
- `Docs/Validation/6-Process.md`
- `Docs/Validation/8-Use_Examples.md`
