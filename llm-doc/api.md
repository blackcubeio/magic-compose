# magic-compose — API

## Traits

| Trait | Provides |
|---|---|
| `MagicComposeTrait` | `__get`, `__set`, `__isset`, `__unset`, `__call` dispatching to handlers |
| `MagicComposeMethodsBaseTrait` | `protected next(mixed ...$args): mixed`, `private executeExtendChain(string $methodName, callable $parent, array $args): mixed` |
| `MagicComposeActiveRecordTrait` | both above + 5 AR methods routed through the chain (`integration.md`) |

## Handler attributes

| Attribute | Magic method | Handler signature |
|---|---|---|
| `#[MagicGetter]` | `__get` | `(string $name): mixed` |
| `#[MagicSetter]` | `__set` | `(string $name, mixed $value): void` |
| `#[MagicIsset]` | `__isset` | `(string $name): bool` |
| `#[MagicUnset]` | `__unset` | `(string $name): void` |
| `#[MagicCall]` | `__call` | `(string $name, array $arguments): mixed` |
| `#[MagicExtend(method: 'x')]` | parent method `x` | same signature as `x` |

- All accept `priority` (default `Priority::NORMAL`). Constants: LOW 10, NORMAL 50, HIGH 90, CRITICAL 100.
- One attribute per method.

## Discovery and sort

- Only `protected` methods are kept. Measured: a `public` or `private` handler, even CRITICAL, is ignored.
- Handlers inherited from a parent class stay active, plus the subclass's own (measured).
- Static cache per class, built on first call.
- Sort by priority descending.
- Magic handlers at equal priority: `use` order (measured both ways: `use FirstTrait; use SecondTrait;` → FirstTrait wins; reversed → SecondTrait wins).
- `MagicExtend` at equal priority: alphabetical order of the method name (measured: `normalA` before `normalB`).

## Dispatch

- Handlers are tried in order; the first one that does not throw `MagicNotHandledException` wins.
- Any other exception propagates.
- `__isset`: `#[MagicIsset]` first, then `#[MagicGetter]` → true if the value is not `null`. Measured: getter returning a value → `isset() === true`; getter returning `null` → `false`; `??` reads the getter's value.

## Fallback when no handler handles

| Method | Parent has the magic method | Parent does not (measured) |
|---|---|---|
| `__get` | `parent::__get()` | `E_USER_WARNING` `Undefined property: X::$name`, returns `null` |
| `__isset` | `parent::__isset()` | same warning, returns `false` (a plain `isset()` triggers the warning) |
| `__set` | `parent::__set()` | warning then `$this->$name = $value`: creates a dynamic property (deprecated in 8.2) or **writes a protected property from outside** |
| `__unset` | `parent::__unset()` | warning then `unset($this->$name)` |
| `__call` | `parent::__call()` | `BadMethodCallException` `Call to undefined method X::name()` |

## `MagicExtend` chain

- Order (measured): HIGH before → normalA before → normalB before → parent → normalB after → normalA after → HIGH after.
- `$this->next(...)` passes on what it is given (measured: `next($value + 1)` → the parent receives 2).
- Return value: what each handler returns, back up the chain.
- A handler that does not call `next()` stops the chain: the parent is not called (measured).
- `next()` outside a chain → `LogicException` `next() called outside of MagicExtend chain`.
- Only methods routed through `executeExtendChain()` are intercepted (`extension.md`).

## Exceptions

| Case | Exception |
|---|---|
| handler does not handle | `MagicNotHandledException` (thrown by the handler, caught by the dispatch) |
| `__call` not handled, no parent | `BadMethodCallException` |
| `next()` outside the chain | `LogicException` |
