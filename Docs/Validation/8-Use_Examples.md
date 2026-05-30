# Validation Use Examples

Subsistema: `Quantum\Validation`

Este documento muestra ejemplos practicos de uso del sistema de validacion de VoltStack.

La idea es recorrer el subsistema desde los casos mas simples hasta los mas expresivos, para que la API pueda entenderse en uso real y no solo a nivel conceptual.

## 1. Objetivo

Esta guia pretende responder a preguntas como:

- como se usa `Validator` en un caso simple
- cuando conviene usar reglas string
- cuando conviene usar reglas objeto
- como personalizar mensajes y atributos
- como validar arrays anidados y wildcards
- como usar la API fluida `Rule`
- como aprovechar la DSL declarativa

## 2. Punto De Partida Minimo

La forma mas simple de validar datos es crear un `Validator` y llamar a `validate(...)`.

```php
<?php

use Quantum\Validation\Validator;

$validator = new Validator();

$validated = $validator->validate([
    'email' => 'user@example.com',
    'name' => 'VoltStack',
], [
    'email' => ['required', 'email'],
    'name' => ['required', 'string', 'min:3'],
]);
```

Si todo es valido, `validate(...)` devuelve el payload validado.

Si algo falla, lanza `ValidationException`.

## 3. Manejo Basico De Errores

El patron mas directo para capturar errores es este:

```php
<?php

use Quantum\Validation\ValidationException;
use Quantum\Validation\Validator;

$validator = new Validator();

try {
    $validated = $validator->validate([
        'email' => 'not-an-email',
    ], [
        'email' => ['required', 'email'],
        'name' => ['required'],
    ]);
} catch (ValidationException $exception) {
    $errors = $exception->errors();

    /*
    [
        'email' => ['The email field must be a valid email address.'],
        'name' => ['The name field is required.'],
    ]
    */
}
```

Este formato de errores es la salida principal del subsistema.

## 4. Reglas String Clasicas

Las reglas string siguen siendo la forma mas compacta para casos sencillos.

```php
<?php

$validated = $validator->validate([
    'title' => 'VoltStack',
    'website' => 'https://voltstack.dev',
    'published_at' => '2026-05-29 10:30:00',
], [
    'title' => 'required|string|max:20',
    'website' => 'required|url',
    'published_at' => 'required|date',
]);
```

Este estilo conviene cuando:

- la validacion es corta
- no hace falta mucha semantica
- se busca una sintaxis compacta

## 5. Mensajes Personalizados Y Atributos

Es posible personalizar tanto mensajes como etiquetas de atributos.

```php
<?php

use Quantum\Validation\ValidationException;
use Quantum\Validation\Validator;

$validator = new Validator();

try {
    $validator->validate([
        'email' => 'not-an-email',
    ], [
        'email' => ['required', 'email'],
        'name' => ['required'],
    ], [
        'email.email' => 'Debes indicar un :attribute valido.',
        'required' => 'El campo :attribute es obligatorio.',
    ], [
        'email' => 'correo electronico',
        'name' => 'nombre completo',
    ]);
} catch (ValidationException $exception) {
    $errors = $exception->errors();

    /*
    [
        'email' => ['Debes indicar un correo electronico valido.'],
        'name' => ['El campo nombre completo es obligatorio.'],
    ]
    */
}
```

Esto es especialmente util cuando el nombre tecnico del campo no debe exponerse al usuario final.

## 6. Control De Flujo Con `bail`

`bail` corta la validacion del campo actual en cuanto una regla falla.

```php
<?php

$validator->validate([
    'email' => 'not-an-email-address',
], [
    'email' => 'bail|email|max:5',
]);
```

En este caso, si `email` falla, `max:5` ya no se evalua para ese mismo campo.

## 7. Corte Temprano Global

`stopOnFirstFailure()` corta el proceso completo en cuanto aparece el primer error.

```php
<?php

$validator
    ->stopOnFirstFailure()
    ->validate([
        'email' => 'not-an-email',
        'website' => 'not-a-url',
    ], [
        'email' => ['required', 'email'],
        'website' => ['required', 'url'],
    ]);
```

Conviene usarlo cuando solo interesa detectar el primer fallo y no construir el mapa completo de errores.

## 8. Callbacks `after()`

Se puede añadir una validacion posterior al ciclo principal con `after()`.

```php
<?php

use Quantum\Validation\ValidationCallbackContext;

$validator
    ->after(static function (ValidationCallbackContext $context): void {
        if (($context->data()['email'] ?? null) === 'taken@example.com') {
            $context->addError('email', 'The email has already been taken.');
        }
    })
    ->validate([
        'email' => 'taken@example.com',
    ], [
        'email' => ['required', 'email'],
    ]);
```

Esto sirve para chequeos posteriores que no encajan bien como una regla simple.

## 9. Mezcla De Reglas String Y Reglas Objeto

El subsistema permite combinar ambos estilos en la misma validacion.

```php
<?php

use Quantum\Validation\Rule;

$validated = $validator->validate([
    'email' => 'user@example.com',
    'name' => 'VoltStack',
    'bio' => 'core team',
], [
    'email' => [Rule::required(), Rule::email()],
    'name' => [Rule::min(3)],
    'bio' => ['string', 'min:4'],
]);
```

Esto permite migrar de forma progresiva desde reglas string hacia una API mas semantica.

## 10. Reglas Objeto Basicas Con `Rule`

`Rule` funciona como factory fluido para reglas reutilizables.

```php
<?php

use Quantum\Validation\Rule;

$validated = $validator->validate([
    'status' => 'published',
    'score' => 7,
    'slug' => 'core-api',
], [
    'status' => [Rule::in(['draft', 'published', 'archived'])],
    'score' => [Rule::between(5, 10)],
    'slug' => [Rule::regex('/^[a-z-]+$/')],
]);
```

Este estilo mejora legibilidad cuando:

- la firma necesita argumentos
- la regla tiene una intencion mas clara como objeto
- se quiere componer con mas API fluida

## 11. Reglas Semanticas

Las reglas semanticas hacen mas legible la intencion de negocio.

```php
<?php

use Quantum\Validation\Rule;

$validated = $validator->validate([
    'terms' => 'yes',
    'opt_out' => 'off',
    'nickname' => null,
    'password' => 'secret123',
    'password_confirmation' => 'secret123',
], [
    'terms' => [Rule::accepted()],
    'opt_out' => [Rule::declined()],
    'nickname' => [Rule::nullable(), Rule::string(), Rule::max(10)],
    'password' => [Rule::confirmed()],
]);
```

Tambien encajan aqui reglas como:

- `Rule::present()`
- `Rule::prohibited()`
- `Rule::same('other_field')`

## 12. Reglas Dependientes

Cuando una regla depende del valor o presencia de otro campo, la API fluida evita caer en strings cada vez mas rigidos.

```php
<?php

use Quantum\Validation\Rule;

$validated = $validator->validate([
    'status' => 'published',
    'published_at' => '2026-05-29',
    'email' => 'user@example.com',
    'phone' => '555-1234',
], [
    'published_at' => [Rule::requiredIf('status', 'published')],
    'support_code' => [Rule::requiredUnless('status', 'archived')],
    'phone' => [Rule::requiredWith('email')],
    'username' => [Rule::requiredWithout('display_name')],
    'internal_notes' => [Rule::prohibitedIf('status', 'draft')],
]);
```

Estas reglas son especialmente utiles en formularios con comportamiento condicional.

## 13. Condiciones Basadas En `bool` O `callable`

Algunas reglas dependientes aceptan tambien booleanos o `callable`.

```php
<?php

use Quantum\Validation\Rule;
use Quantum\Validation\ValidationRuleContext;

$validated = $validator->validate([
    'flagged' => true,
    'review_notes' => 'Pending manual review',
], [
    'review_notes' => [
        Rule::requiredIf(
            static fn (ValidationRuleContext $context): bool =>
                ($context->data()['flagged'] ?? false) === true
        ),
    ],
    'audit_token' => [Rule::requiredIf(true)],
]);
```

Esto permite construir condiciones mas expresivas sin tocar `Validator`.

## 14. Arrays Anidados Y Wildcards

El sistema soporta rutas con dot notation y wildcards.

```php
<?php

$validated = $validator->validate([
    'profile' => [
        'name' => 'VoltStack',
    ],
    'items' => [
        ['name' => 'core_api', 'price' => '10.5'],
        ['name' => 'admin-ui', 'price' => 20],
    ],
], [
    'profile.name' => ['required', 'string', 'min:3'],
    'items.*.name' => ['required', 'alpha_dash'],
    'items.*.price' => ['required', 'numeric'],
]);
```

Cada `items.*` se resuelve contra el indice correspondiente.

## 15. Wildcards Con Campos Dependientes

Una de las capacidades mas utiles del subsistema es resolver correctamente campos relacionados dentro de estructuras anidadas.

```php
<?php

use Quantum\Validation\Rule;

$validated = $validator->validate([
    'items' => [
        [
            'value' => 'alpha',
            'value_repeat' => 'alpha',
        ],
    ],
], [
    'items.*.value' => [Rule::confirmed('items.*.value_repeat')],
]);
```

Cuando se valida `items.0.value`, el sistema resuelve automaticamente `items.0.value_repeat`.

## 16. Condiciones Declarativas Con `when(...)`

La DSL declarativa empieza con `Rule::when('field')`.

```php
<?php

use Quantum\Validation\Rule;

$validated = $validator->validate([
    'status' => 'draft',
], [
    'published_at' => [
        Rule::when('status')->is('draft')->prohibited(),
    ],
]);
```

Tambien puede expresarse una obligacion:

```php
<?php

'delivery_window' => [
    Rule::when('delivery_mode')->isNot('auto')->thenRequired(),
],
```

La ventaja de esta DSL es que separa:

- la condicion
- la accion de validacion

## 17. Condiciones Por Estado Del Campo

No todas las condiciones dependen del valor exacto.

Tambien pueden depender de si un campo existe, falta, esta vacio o lleno.

```php
<?php

use Quantum\Validation\Rule;

$validated = $validator->validate([
    'reviewer_id' => 42,
    'approval_code' => 'APR-001',
    'notes' => 'manual review',
    'follow_up_ack' => 'yes',
    'coupon' => '',
], [
    'approval_code' => [Rule::when('reviewer_id')->exists()->thenRequired()],
    'retry_reason' => [Rule::when('failure_code')->missing()->thenProhibited()],
    'follow_up_ack' => [Rule::when('notes')->filled()->thenAccepted()],
    'coupon_audit' => [Rule::when('coupon')->empty()->thenProhibited()],
]);
```

Esto hace que la API sea mas declarativa y semantica.

## 18. Reutilizar Condiciones

Una condicion declarativa puede construirse una vez y reutilizarse varias veces.

```php
<?php

use Quantum\Validation\Rule;

$published = Rule::when('status')->is('published');

$validated = $validator->validate([
    'status' => 'published',
    'marketing_opt_in' => 'yes',
    'compliance_check' => 'yes',
], [
    'marketing_opt_in' => [Rule::acceptedIf($published)],
    'compliance_check' => [$published->accepted()],
]);
```

Esto evita repetir la misma semantica en varias reglas.

## 19. Composicion De Condiciones

Si una sola condicion no basta, se puede componer con `allOf(...)`, `anyOf(...)`, `andWhen(...)` y `orWhen(...)`.

### 19.1 `allOf(...)`

```php
<?php

use Quantum\Validation\Rule;

$manualAndRestricted = Rule::allOf(
    Rule::when('delivery_mode')->is('manual'),
    Rule::when('visibility')->notIn('public', 'private'),
);

$rules = [
    'security_flag' => [$manualAndRestricted->thenProhibited()],
];
```

### 19.2 `anyOf(...)`

```php
<?php

use Quantum\Validation\Rule;

$archivedOrMissingCoupon = Rule::anyOf(
    Rule::when('status')->is('archived'),
    Rule::when('coupon')->missing(),
);

$rules = [
    'coupon_guard' => [$archivedOrMissingCoupon->thenAccepted()],
];
```

### 19.3 `andWhen(...)`

```php
<?php

use Quantum\Validation\Rule;

$rules = [
    'override_reason' => [
        Rule::when('delivery_mode')->is('manual')
            ->andWhen('reviewer_id')->exists()
            ->thenRequired(),
    ],
];
```

## 20. Mensajes Personalizados Con Reglas Objeto

Las reglas objeto siguen usando el mismo sistema de mensajes por `field.rule`.

```php
<?php

use Quantum\Validation\Rule;
use Quantum\Validation\ValidationException;

try {
    $validator->validate([
        'status' => 'pending',
        'score' => 12,
    ], [
        'status' => [Rule::in(['draft', 'published', 'archived'])],
        'score' => [Rule::between(5, 10)],
    ], [
        'status.in' => 'El :attribute seleccionado no es valido.',
        'score.between' => 'El :attribute debe estar entre :min y :max.',
    ], [
        'status' => 'estado',
        'score' => 'puntaje',
    ]);
} catch (ValidationException $exception) {
    $errors = $exception->errors();
}
```

Esto mantiene una experiencia consistente entre reglas string y reglas objeto.

## 21. Ejemplo Completo De API Fluida

El siguiente ejemplo mezcla varias capacidades modernas del subsistema:

```php
<?php

use Quantum\Validation\Rule;

$published = Rule::when('status')->is('published');
$manualDelivery = Rule::when('delivery_mode')->isNot('auto');
$reviewerExists = Rule::when('reviewer_id')->exists();

$validated = $validator->validate([
    'terms' => 'yes',
    'status' => 'published',
    'delivery_mode' => 'manual',
    'delivery_window' => '9-5',
    'reviewer_id' => 42,
    'approval_code' => 'APR-001',
    'marketing_opt_in' => 'yes',
    'password' => 'secret123',
    'password_confirmation' => 'secret123',
], [
    'terms' => [Rule::accepted()],
    'delivery_window' => [$manualDelivery->thenRequired()],
    'approval_code' => [$reviewerExists->thenRequired()],
    'marketing_opt_in' => [Rule::acceptedIf($published)],
    'password' => [Rule::confirmed()],
    'internal_code' => [Rule::requiredIf('status', 'published')],
]);
```

Este tipo de definicion suele ser mas facil de mantener que una lista larga de strings opacos.

## 22. Cuando Usar Cada Estilo

Una guia practica razonable es esta:

### 22.1 Usar Reglas String Cuando

- el caso es corto
- la validacion es directa
- no hace falta mucha semantica

### 22.2 Usar Reglas Objeto Cuando

- la regla tiene una intencion mas clara como objeto
- hay argumentos expresivos
- quieres una API mas legible

### 22.3 Usar DSL Declarativa Cuando

- la validacion depende de condiciones de negocio
- una regla depende de otra señal del payload
- la lectura mejora claramente frente a strings condicionales

## 23. Recomendaciones Practicas

- empezar simple y solo subir de nivel cuando el caso lo pida
- usar mensajes personalizados cuando el texto vaya al usuario final
- usar alias de atributos para no exponer nombres tecnicos
- usar reglas objeto y DSL cuando la semantica dependiente se vuelva importante
- probar bien los casos con wildcards y dependencias

## 24. Resumen

`Quantum\Validation` puede usarse en varios niveles de expresividad:

1. reglas string compactas
2. reglas objeto con `Rule`
3. reglas dependientes expresivas
4. DSL declarativa composable

La clave es que todos esos niveles conviven dentro del mismo motor.

## 25. Documentos Relacionados

- `Docs/Validation/1-Validation_Context.md`
- `Docs/Validation/2-Architecture.md`
- `Docs/Validation/6-Process.md`
- `Docs/Validation/7-Tests.md`
