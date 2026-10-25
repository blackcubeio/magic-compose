# Blackcube Magic Compose

> **⚠️ Blackcube Warning**
>
> PHP says one `__get` per class. Blackcube says no.
>
> Multiple traits, each with their own magic handlers, dispatched by priority via attributes.
> It's not a hack — it's composition where PHP forgot to provide it.

Simple solution for magic methods and method composition using attributes.

[![License](https://img.shields.io/badge/license-BSD--3--Clause-blue.svg)](LICENSE.md)
[![Packagist Version](https://img.shields.io/packagist/v/blackcube/magic-compose.svg)](https://packagist.org/packages/blackcube/magic-compose)
[![Warning](https://img.shields.io/badge/Blackcube-Warning-orange)](BLACKCUBE_WARNING.md)

## Installation

```bash
composer require blackcube/magic-compose
```

## Problem

PHP traits can't compose magic methods. When multiple traits define `__get`, only one wins:

```php
class MyClass {
    use TraitA, TraitB; // TraitB::__get shadows TraitA::__get
}
```

## Solution

Mark handlers with attributes, let `MagicComposeTrait` dispatch:

```php
class MyClass {
    use MagicComposeTrait, TraitA, TraitB;
}

trait TraitA {
    #[MagicGetter]
    protected function getFromA(string $name): mixed {
        if ($name === 'foo') return 'from A';
        throw new MagicNotHandledException();
    }
}

trait TraitB {
    #[MagicGetter(priority: Priority::HIGH)]
    protected function getFromB(string $name): mixed {
        if ($name === 'bar') return 'from B';
        throw new MagicNotHandledException();
    }
}

$obj = new MyClass();
$obj->foo; // 'from A'
$obj->bar; // 'from B'
```

## Attributes

| Attribute | Magic method | Handler signature |
|-----------|--------------|-------------------|
| `#[MagicGetter]` | `__get` | `(string $name): mixed` |
| `#[MagicSetter]` | `__set` | `(string $name, mixed $value): void` |
| `#[MagicIsset]` | `__isset` | `(string $name): bool` |
| `#[MagicUnset]` | `__unset` | `(string $name): void` |
| `#[MagicCall]` | `__call` | `(string $name, array $arguments): mixed` |

## Priority

Handlers are sorted by priority (highest first):

```php
use Blackcube\MagicCompose\Attributes\Priority;

#[MagicGetter(priority: Priority::HIGH)]  // 90 - runs first
#[MagicGetter(priority: Priority::NORMAL)] // 50 - default
#[MagicGetter(priority: Priority::LOW)]    // 10 - runs last
```

Constants: `CRITICAL` (100), `HIGH` (90), `NORMAL` (50), `LOW` (10).

## MagicExtend

Intercept parent class methods with chainable `$this->next()`:

```php
trait AuditTrait {
    use MagicComposeMethodsBaseTrait;

    #[MagicExtend(method: 'deleteInternal')]
    protected function auditDelete(): int {
        $this->log('before delete');
        $result = $this->next(); // calls parent::deleteInternal() or next handler
        $this->log('after delete');
        return $result;
    }
}
```

`MagicComposeActiveRecordTrait` routes five ActiveRecord methods through the extend chain: `propertyValuesInternal`, `refreshInternal`, `populateProperty`, `deleteInternal` and `populateRecord`. To extend another method, override it and run it through `executeExtendChain()`.

## ActiveRecord Integration

For Yii ActiveRecord, use `MagicComposeActiveRecordTrait`:

```php
use Yiisoft\ActiveRecord\ActiveRecord;
use Blackcube\MagicCompose\MagicComposeActiveRecordTrait;

class User extends ActiveRecord {
    use MagicComposeActiveRecordTrait;
    use SomeCustomTrait;
}
```

Pre-wired methods: `propertyValuesInternal`, `refreshInternal`, `populateProperty`, `deleteInternal`, `populateRecord`.

## Handler Flow

1. Handlers discovered via reflection (cached)
2. Sorted by priority DESC
3. First handler that doesn't throw `MagicNotHandledException` wins
4. If all throw → fallback to parent or PHP error

## License

BSD-3-Clause. See [LICENSE.md](LICENSE.md).

## Author

Philippe Gaultier <philippe@blackcube.io>