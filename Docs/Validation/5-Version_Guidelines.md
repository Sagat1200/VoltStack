# Validation Version Guidelines

Subsistema: `Quantum\Validation`

Este documento define las reglas de versionado y compatibilidad para el sistema de validacion de VoltStack.

Su objetivo es ofrecer criterios claros para decidir:

- que cambios son compatibles
- que cambios deben considerarse breaking changes
- como introducir mejoras sin desestabilizar la API publica
- como proteger el comportamiento semantico del subsistema

## 1. Por Que Esta Guia Existe

En `Quantum\Validation`, los cambios no afectan solo firmas o tipos.

Tambien pueden afectar:

- semantica de reglas
- mensajes de error
- placeholders
- orden de evaluacion
- comportamiento con wildcards
- contratos implícitos entre `Validator`, `Rule` y reglas custom

Por eso este subsistema necesita una guia de versionado mas estricta que un simple "si compila, no rompe".

## 2. Alcance

Estas guias aplican a:

- `ValidatorInterface`
- `Validator`
- `RuleInterface`
- `Rule`
- `ValidationRuleContext`
- `ValidationException`
- reglas incluidas en `Rules/`
- condiciones declarativas en `Conditions/`
- placeholders y convenciones de mensajes

## 3. Principio General

La regla principal es esta:

Un cambio en validacion debe considerarse breaking si puede alterar el resultado observable de un consumidor existente sin que este cambie su codigo.

Ese resultado observable incluye:

- si valida o no valida
- que errores devuelve
- que mensaje produce
- que placeholders rellena
- en que momento corta la ejecucion
- como resuelve dependencias y wildcards

## 4. Superficies De Compatibilidad

La compatibilidad del subsistema debe evaluarse sobre varias superficies.

### 4.1 API De Codigo

Incluye:

- firmas publicas
- nombres de metodos
- clases expuestas
- contratos e interfaces

### 4.2 API Semantica

Incluye:

- significado de una regla
- interpretacion de valores
- comportamiento de las condiciones
- resolucion de dependencias

### 4.3 API De Mensajes

Incluye:

- nombres de regla usados en mensajes
- placeholders disponibles
- convenciones `:value` y `:values`
- mapeo de atributos

### 4.4 API De Flujo

Incluye:

- orden de evaluacion
- comportamiento de `bail`
- `stopOnFirstFailure()`
- `skipRemainingRules()`
- callbacks `after()`

## 5. Regla De Oro Del Versionado

En este subsistema:

- cambios compatibles van a versiones menores o parches
- cambios de comportamiento observable van a versiones mayores

Si existe duda razonable, debe asumirse que el cambio es breaking hasta demostrar lo contrario.

## 6. Cambios Compatibles

Los siguientes cambios suelen ser compatibles.

### 6.1 Nuevas Reglas Publicas

Añadir nuevas reglas sin cambiar el comportamiento de las existentes suele ser compatible.

Ejemplos:

- nueva clase de regla
- nuevo factory method en `Rule`
- nueva condicion declarativa

Condicion:

- no debe cambiar el significado de reglas ya existentes

### 6.2 Nuevos Helpers Internos

Es compatible añadir:

- traits internos
- abstracciones privadas o protegidas
- clases de soporte no documentadas como API publica

Condicion:

- no deben romper extensiones razonables existentes

### 6.3 Nuevos Placeholders Opcionales

Se pueden añadir placeholders nuevos en contextos donde antes no existian, siempre que:

- no cambien placeholders previos
- no rompan renderizado existente

### 6.4 Mejoras De Cobertura O Documentacion

Es compatible:

- ampliar tests
- mejorar docs
- aclarar ejemplos

### 6.5 Opt-In Features

Es compatible añadir comportamiento nuevo si es completamente opt-in.

Ejemplos:

- nuevo metodo fluido
- nueva clase de condicion
- nuevo modo explicitamente activado

## 7. Cambios Que Deben Considerarse Breaking

Los siguientes cambios deben tratarse como breaking salvo una justificacion muy fuerte.

### 7.1 Cambios En Firmas Publicas

Ejemplos:

- cambiar parametros de `ValidatorInterface`
- cambiar el contrato de `RuleInterface`
- eliminar o renombrar metodos de `Rule`
- modificar la firma de `ValidationRuleContext`

### 7.2 Cambios De Semantica En Reglas Existentes

Ejemplos:

- cambiar que valores acepta `accepted()`
- cambiar que significa "empty"
- modificar como compara `same()`
- alterar cuando `nullable()` corta reglas restantes

### 7.3 Cambios En Mensajes O Placeholders Existentes

Debe considerarse breaking:

- eliminar `:other`, `:value` o `:values` en reglas donde existian
- cambiar el nombre normalizado de una regla usada por mensajes custom
- alterar de forma incompatible el contenido esperado de placeholders

### 7.4 Cambios En Evaluacion O Flujo

Ejemplos:

- alterar el orden de evaluacion de reglas
- cambiar el efecto de `bail`
- cambiar cuando se ejecutan callbacks `after()`
- cambiar cuando una regla deja de evaluar las siguientes

### 7.5 Cambios En Resolucion De Wildcards

La resolucion de campos dependientes forma parte de la semantica observable.

Si cambia la forma en que se resuelven:

- `items.*.value`
- confirmaciones dependientes
- condiciones sobre arrays anidados

el cambio debe asumirse como breaking.

## 8. Cambios Que Parecen Pequeños Pero No Lo Son

En validacion hay cambios aparentemente menores que en realidad son de alto impacto.

### 8.1 Tocar El Set De Valores Aceptados

Cambio de ejemplo:

- aceptar un nuevo valor en `accepted()`
- dejar de aceptar un valor en `declined()`

Eso altera reglas de negocio reales.

Debe tratarse con mucho cuidado.

### 8.2 Ajustar "empty"

Cambiar si algo como `0`, `'0'`, `false`, `[]` o `null` cuenta como vacio puede afectar múltiples reglas:

- `required`
- `nullable`
- `prohibited`
- reglas dependientes
- condiciones `empty()` y `filled()`

Es un area de alto riesgo.

### 8.3 Renombrar Reglas O Rule Names

Aunque parezca una mejora interna, el nombre de regla impacta:

- mensajes custom
- tests de consumidores
- reglas custom que replican convenciones

### 8.4 Cambiar Mensajes Default

No siempre es breaking a nivel binario, pero puede romper:

- snapshots
- tests de integración
- UI que depende de mensajes exactos

La recomendacion es considerarlo cambio sensible y documentarlo siempre.

## 9. Politica Por Tipo De Version

### 9.1 Patch

Una version patch solo debe incluir:

- fixes de bugs claramente incorrectos
- mejoras internas sin impacto observable
- ajustes de documentacion
- tests adicionales

Un patch no debe:

- cambiar semantica establecida
- cambiar placeholders
- cambiar firmas publicas

### 9.2 Minor

Una version minor puede incluir:

- nuevas reglas
- nuevas condiciones declarativas
- nuevas abstractions internas
- nuevas integraciones opt-in
- mejoras de DX compatibles

Una minor no debe:

- romper consumidores existentes
- alterar semantica consolidada

### 9.3 Major

Una version major es obligatoria cuando cambia:

- la API publica
- la semantica observable de reglas existentes
- la estrategia de mensajes
- la resolucion de dependencias o wildcards
- el contrato general de extensibilidad

## 10. Reglas Especificas Por Componente

### 10.1 `ValidatorInterface`

Muy estable.

Cualquier cambio en su firma debe considerarse major.

### 10.2 `Validator`

Su comportamiento observable es parte de la API, aunque parte de su implementacion interna no lo sea.

Cambios sensibles:

- orden de evaluacion
- excepciones lanzadas
- formato de errores
- comportamiento de `after()`

### 10.3 `RuleInterface`

Contrato critico para extensibilidad.

Cualquier cambio aqui es major.

### 10.4 `Rule`

La adicion de nuevos metodos puede ser minor.

Eliminar, renombrar o cambiar semantica de metodos existentes es major.

### 10.5 `ValidationRuleContext`

Es una superficie de extensibilidad real para reglas custom.

Cambiar su API publica debe tratarse como cambio mayor.

Añadir metodos nuevos puede ser compatible si no altera comportamiento previo.

### 10.6 `ValidationException`

Cambiar:

- la estructura de `errors()`
- el status asociado
- el mensaje base

puede ser breaking segun el impacto observable y debe evaluarse con criterio conservador.

## 11. Reglas Especificas Para La DSL Declarativa

La DSL tiene una semantica especialmente delicada.

### 11.1 Compatible

- añadir nuevos builders
- añadir nuevos combinadores opt-in
- añadir nuevas condiciones reutilizables

### 11.2 Breaking

- cambiar el significado de `is`, `isNot`, `in`, `notIn`
- cambiar semantica de `exists`, `missing`, `empty`, `filled`
- cambiar como funciona `allOf`, `anyOf`, `andWhen`, `orWhen`
- cambiar como una condicion alimenta placeholders o labels

### 11.3 Regla Practica

Si una condicion declarativa ya puede aparecer dentro de `acceptedIf(...)`, `requiredIf(...)` o `prohibitedIf(...)`, su semantica debe considerarse estable.

## 12. Compatibilidad Con Reglas Custom

El subsistema debe asumir que existen consumidores con:

- reglas propias que implementan `RuleInterface`
- wrappers sobre `Rule`
- pruebas sobre mensajes
- convenciones locales de placeholders

Por tanto:

- no se debe introducir cambios casuales en contratos
- no se debe asumir que "interno" significa "sin consumidores"
- las refactorizaciones deben revisar impacto en extensibilidad

## 13. Politica De Deprecacion

Cuando un cambio no puede mantenerse para siempre, debe seguirse una politica de deprecacion clara.

### 13.1 Etapas Recomendadas

1. documentar la API nueva
2. marcar la vieja como deprecada en documentacion
3. mantener compatibilidad durante al menos un ciclo razonable
4. eliminar en major

### 13.2 Donde Anunciar La Deprecacion

- documentacion del subsistema
- changelog del framework
- notas de version
- ejemplos actualizados

### 13.3 Lo Que No Debe Hacerse

No debe eliminarse una forma publica de uso sin:

- ruta de migracion
- documentacion
- ventana razonable de transicion

## 14. Criterios Para Aprobar Cambios

Antes de aceptar un cambio en `Quantum\Validation`, conviene responder:

1. cambia algun resultado observable?
2. cambia algun mensaje o placeholder?
3. cambia la semantica de una regla existente?
4. afecta wildcards o dependencias?
5. afecta reglas custom o extensibilidad?
6. requiere nota de migracion?

Si la respuesta a cualquiera de estas preguntas es "si", el cambio debe tratarse con disciplina de versionado.

## 15. Matriz Rapida De Decisiones

| Tipo de cambio | Normalmente |
| --- | --- |
| Nueva regla opt-in | minor |
| Nueva condicion declarativa | minor |
| Nuevo helper interno | patch o minor |
| Fix claramente incorrecto sin cambio semantico esperado | patch |
| Cambio de firma publica | major |
| Cambio en placeholders existentes | major |
| Cambio en `empty` o `accepted` | major |
| Cambio en evaluacion de wildcards | major |
| Cambio de mensaje default | sensible, documentar siempre |

## 16. Recomendaciones Operativas

Para mantener un versionado sano en este subsistema:

- priorizar tests de comportamiento, no solo de estructura
- documentar cambios semanticos aunque parezcan pequeños
- evitar refactors que mezclen mejoras internas con cambios públicos
- separar nuevas APIs de cambios correctivos
- acompañar breaking changes con ejemplos de migracion

## 17. Tesis Final

La estabilidad de `Quantum\Validation` no depende solo de que sus clases sigan existiendo.

Depende de que su semantica siga siendo predecible.

En este subsistema, versionar bien significa proteger:

- contratos
- comportamiento
- mensajes
- extensibilidad
- confianza del consumidor

## 18. Documentos Relacionados

- `Docs/Validation/1-Validation_Context.md`
- `Docs/Validation/2-Architecture.md`
- `Docs/Validation/3-Roadmap.md`
- `Docs/Validation/4-Evolution_Prospect.md`
- `Docs/Validation/6-Process.md`
- `Docs/Validation/7-Tests.md`
- `Docs/Validation/8-Use_Examples.md`
