# magic-compose — Extension

## 1. Add a handler trait

- `protected` method + attribute; throws `MagicNotHandledException` for any name it does not handle.
- Add the trait to the class `use` list, next to `MagicComposeTrait` (or `MagicComposeActiveRecordTrait` in `Xxx`).

```php
trait MetadataTrait
{
    private array $metadata = [];

    #[MagicGetter]
    protected function getMetadata(string $name): mixed
    {
        if (array_key_exists($name, $this->metadata) === false) {
            throw new MagicNotHandledException();
        }
        return $this->metadata[$name];
    }

    #[MagicSetter]
    protected function setMetadata(string $name, mixed $value): void
    {
        if (str_starts_with($name, 'meta_') === false) {
            throw new MagicNotHandledException();
        }
        $this->metadata[$name] = $value;
    }
}
```

Measured: `$document->meta_title = 'Hello'` then `$document->meta_title` → `'Hello'`; `isset()` → `true` (through the getter).

## 2. Intercept a non-wired method

In the class that does `use MagicComposeMethodsBaseTrait` (or `MagicComposeActiveRecordTrait`):

1. override the parent method and route it through `executeExtendChain()`;
2. declare `#[MagicExtend(method: '<name>')]` handlers, `protected`, same signature.

```php
protected function compute(int $value): int
{
    return $this->executeExtendChain('compute', fn($v) => parent::compute($v), [$value]);
}

#[MagicExtend(method: 'compute')]
protected function auditCompute(int $value): int
{
    $result = $this->next($value);
    // ...
    return $result;
}
```

- Measured: order, arguments, short-circuit (`api.md`).
- `executeExtendChain()` is `private` in the trait: callable only from the class that does the `use`.

## 3. Not extensible

- New attribute type: a subclass of `MagicGetter` (or of any other attribute) is ignored; `getAttributes()` without `IS_INSTANCEOF` = exact class (`MagicComposeTrait.php:65`). Measured: `#[CustomGetter]` (subclass of `MagicGetter`) → `Undefined property`.
- List of magic methods: fixed, the 5 coded in `MagicComposeTrait`; `__callStatic` is not dispatched.
- Priority: `priority` is an `int`; the `Priority` constants are landmarks.
