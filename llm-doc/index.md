# blackcube/magic-compose — LLM index

**Role:** compose several traits that each declare magic methods (`__get`, `__set`, `__isset`, `__unset`, `__call`) in one class, and intercept parent methods through a chain (`#[MagicExtend]` + `next()`).

## Package card

| | |
|---|---|
| Namespace | `Blackcube\MagicCompose` |
| Traits | `MagicComposeTrait` (dispatch of the 5 magic methods), `MagicComposeMethodsBaseTrait` (`MagicExtend` chain, `next()`, `executeExtendChain()`), `MagicComposeActiveRecordTrait` (both + 5 wired AR methods) |
| Attributes | `MagicGetter`, `MagicSetter`, `MagicIsset`, `MagicUnset`, `MagicCall`, `MagicExtend` (abstract base `MagicAttribute`); `Priority` constants: LOW 10, NORMAL 50, HIGH 90, CRITICAL 100 |
| Exception | `MagicNotHandledException`: a handler throws it to pass |
| Requires | PHP ^8.2. No dependency. `yiisoft/active-record` ^1.1 only for `MagicComposeActiveRecordTrait` (suggest). |
| License | BSD-3-Clause |

## Rules

- A handler is a **protected** method carrying the attribute; public or private → ignored (`MagicComposeTrait.php:64`, `MagicComposeMethodsBaseTrait.php:64`).
- A handler that does not handle the name throws `MagicNotHandledException`; never returns `null` to mean "not mine".
- AR with Elastic and/or Hazeltree: use the composite traits of active-record (`ElasticTrait`, `HazeltreeTrait`, `HazeltreeElasticTrait`), without MagicCompose (`active-record/src/MagicComposeActiveRecordTrait.php:18-21`).
- `MagicComposeActiveRecordTrait` goes in the child class (`Xxx`), native AR traits in the base class (`BaseXxx`) (`docs/integration.md:102`).

## Parts

| File | Content | Read when |
|---|---|---|
| `api.md` | The 3 traits, the 6 attributes, `Priority`, the exception. Dispatch flow: reflection discovery (protected methods, first attribute only, cache per class), sort by priority descending (tie: reflection order; `MagicExtend`: alphabetical), the first handler that does not throw wins. `__isset` also asks the `MagicGetter` handlers (non-null value). Fallback: the parent's magic method, otherwise a warning (`__get`/`__set`/`__isset`/`__unset`) or `BadMethodCallException` (`__call`). `MagicExtend` chain: execution order, `next()`, `LogicException` outside the chain. | before writing a handler |
| `integration.md` | Plain PHP class; Yii AR: `BaseXxx`/`Xxx`, the 5 wired methods (`propertyValuesInternal`, `refreshInternal`, `populateProperty`, `deleteInternal`, `populateRecord`); when not to use it (active-record composite traits). | before plugging MagicCompose in |
| `extension.md` | Add a handler trait; intercept a non-wired method (override + `executeExtendChain()`). Not extensible: an attribute subclass is not recognised (`getAttributes()` without `IS_INSTANCEOF` = exact class). | before extending |
