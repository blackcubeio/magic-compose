# API

## Traits

### MagicComposeTrait

Auto-discovers and dispatches magic methods based on attributes. Add this trait to your class alongside handler traits.

```php
class MyClass {
    use MagicComposeTrait, TraitA, TraitB;
}
```

Dispatches `__get`, `__set`, `__isset`, `__unset`, and `__call` to handlers sorted by priority (highest first). If a handler throws `MagicNotHandledException`, the chain continues to the next handler. If all handlers throw, falls back to parent magic methods or triggers a PHP warning/error.

`__isset` has one more step: with no `#[MagicIsset]` handler answering, it asks the `#[MagicGetter]` ones and reports the property as set when a getter returns a non-null value. A trait exposing a value for reading has therefore nothing more to declare — which matters because `$object->property ?? null`, written outside the trait, calls `__isset` first and never reaches `__get` when it answers false.

### MagicComposeMethodsBaseTrait

Provides `MagicExtend` discovery and chain execution. Used by traits that need to intercept parent class methods.

| Method | Description |
|--------|-------------|
| `next(mixed ...$args): mixed` | Call the next handler in the chain (must be called from a `MagicExtend` handler) |

### MagicComposeActiveRecordTrait

Pre-wired bridge for Yii3 ActiveRecord. Wraps AR operations through the `MagicExtend` chain:

- `propertyValuesInternal()` — called on save
- `populateRecord(array|object $row)` — called on load from DB
- `populateProperty(string $name, mixed $value)` — called for each column on load
- `refreshInternal()` — called on `refresh()`
- `deleteInternal()` — called on `delete()`

Requires `yiisoft/active-record` (see `suggest` in `composer.json`).

```php
use Blackcube\MagicCompose\MagicComposeActiveRecordTrait;

class Product extends ActiveRecord {
    use MagicComposeActiveRecordTrait, CustomTrait;
}
```

## Attributes

### Magic method attributes

| Attribute | Magic method | Handler signature |
|-----------|--------------|-------------------|
| `#[MagicGetter]` | `__get` | `(string $name): mixed` |
| `#[MagicSetter]` | `__set` | `(string $name, mixed $value): void` |
| `#[MagicIsset]` | `__isset` | `(string $name): bool` |
| `#[MagicUnset]` | `__unset` | `(string $name): void` |
| `#[MagicCall]` | `__call` | `(string $name, array $arguments): mixed` |

All accept an optional `priority` parameter (default `Priority::NORMAL`).

### MagicExtend

Marks a method as an override handler for a parent class method. Handler signature must match the original method. Use `$this->next(...)` to call the next handler in chain.

```php
#[MagicExtend(method: 'deleteInternal', priority: Priority::HIGH)]
protected function auditDelete(): int {
    $this->log('before delete');
    $result = $this->next();
    $this->log('after delete');
    return $result;
}
```

### Priority

Constants for handler ordering (highest runs first):

| Constant | Value |
|----------|-------|
| `Priority::CRITICAL` | 100 |
| `Priority::HIGH` | 90 |
| `Priority::NORMAL` | 50 |
| `Priority::LOW` | 10 |

When priority is equal, `MagicExtend` handlers are sorted alphabetically by method name; magic method handlers keep the reflection order.

## Exceptions

### MagicNotHandledException

Thrown by a magic handler to signal it doesn't handle the property/method. The chain continues to the next handler.

```php
#[MagicGetter(Priority::HIGH)]
protected function getFromA(string $name): mixed {
    if ($name === 'foo') return 'from A';
    throw new MagicNotHandledException();
}
```

## Handler flow

1. Handlers discovered via reflection (cached per class)
2. Sorted by priority DESC (`MagicExtend` handlers tie-break alphabetically by method name)
3. First handler that doesn't throw `MagicNotHandledException` wins
4. If all throw → fallback to parent magic method or PHP warning/error
