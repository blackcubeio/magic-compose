# magic-compose — Integration

## 1. Plain PHP class

```php
class Document
{
    use MagicComposeTrait;
    use MetadataTrait;
    use AuditTrait;
}
```

- At equal priority, the `use` order is the handler order (`api.md`).
- No magic parent: an unhandled name → the fallbacks of `api.md` (warning, or `BadMethodCallException` for `__call`).
- Parent with magic methods: an unhandled name is passed to it (`MagicComposeTrait.php:118-119`).

## 2. Yii ActiveRecord: `BaseXxx` / `Xxx`

| Class | Content |
|---|---|
| `BaseXxx extends ActiveRecord` | native AR traits (`MagicPropertiesTrait`, `MagicRelationsTrait`, `EventsTrait`…), properties, relations |
| `Xxx extends BaseXxx` | `MagicComposeActiveRecordTrait` + handler traits |

- Both traits in the same class = fatal error (measured): `Trait method …MagicComposeActiveRecordTrait::propertyValuesInternal has not been applied … because of collision with …MagicPropertiesTrait::propertyValuesInternal`.
- In `Xxx`, a name not handled by the handlers falls back to the AR magic method of `BaseXxx`.
- Two identical copies: `Blackcube\MagicCompose\MagicComposeActiveRecordTrait` and `Blackcube\ActiveRecord\MagicComposeActiveRecordTrait` (same body, measured by diff). The active-record copy requires magic-compose to be installed (`active-record/composer.json:50`, suggest).

## 3. The 5 wired AR methods

Calls found in `yiisoft/active-record`:

| Method | Called by |
|---|---|
| `propertyValuesInternal()` | every value read: `get()`, `propertyValues()`, `isPropertyChanged()`, `isPropertyChangedNonStrict()`, `markAsExisting()` (`AbstractActiveRecord.php:95,106,255,266,500`) |
| `populateRecord(array\|object $row)` | each row loaded by a query (`ActiveQuery.php:712`) |
| `populateProperty(string $name, mixed $value)` | `populateRecord()`, `set()`, `populateProperties()`, `updateCounters()`, `refreshInternal()` (`AbstractActiveRecord.php:404,482,489,562,925`) |
| `refreshInternal(...)` | `refresh()` (`AbstractActiveRecord.php:437`) |
| `deleteInternal()` | `delete()` (`AbstractActiveRecord.php:73`) |

Intercept them with `#[MagicExtend(method: '<name>')]` in a trait of `Xxx`.

## 4. When not to use it

- Elastic and/or Hazeltree: the composite traits of `blackcube/active-record` (`ElasticTrait`, `HazeltreeTrait`, `HazeltreeElasticTrait`) dispatch directly, without MagicCompose (`active-record/src/MagicComposeActiveRecordTrait.php:18-21`). dcore models: `Bloc` → `ElasticTrait`, `Content` / `Tag` → `HazeltreeElasticTrait`.
