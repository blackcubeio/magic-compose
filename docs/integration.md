# Integration

## PSR / generic PHP

Magic Compose is framework-agnostic. Use `MagicComposeTrait` in any PHP class:

```php
use Blackcube\MagicCompose\MagicComposeTrait;
use Blackcube\MagicCompose\Attributes\MagicGetter;
use Blackcube\MagicCompose\Attributes\MagicSetter;
use Blackcube\MagicCompose\Attributes\Priority;
use Blackcube\MagicCompose\Exceptions\MagicNotHandledException;

trait MetadataTrait {
    private array $metadata = [];

    #[MagicGetter(Priority::NORMAL)]
    protected function getMetadata(string $name): mixed {
        if (array_key_exists($name, $this->metadata)) {
            return $this->metadata[$name];
        }
        throw new MagicNotHandledException();
    }

    #[MagicSetter(Priority::NORMAL)]
    protected function setMetadata(string $name, mixed $value): void {
        if (str_starts_with($name, 'meta_')) {
            $this->metadata[$name] = $value;
            return;
        }
        throw new MagicNotHandledException();
    }
}

class Document {
    use MagicComposeTrait, MetadataTrait;
}

$doc = new Document();
$doc->meta_title = 'Hello';    // handled by MetadataTrait
$doc->meta_title;              // 'Hello'
```

### MagicExtend for method interception

Use `MagicComposeMethodsBaseTrait` with `#[MagicExtend]` to intercept parent methods:

```php
use Blackcube\MagicCompose\MagicComposeMethodsBaseTrait;
use Blackcube\MagicCompose\Attributes\MagicExtend;

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

## Yii

### ActiveRecord composition

Use `MagicComposeActiveRecordTrait` in child AR classes. This trait combines `MagicComposeTrait` + `MagicComposeMethodsBaseTrait` with pre-wired AR method interception.

The pattern is **BaseXxx / Xxx**:

- **BaseXxx** — native AR traits (`EventsTrait`, `MagicRelationsTrait`, `MagicPropertiesTrait`), properties, relations
- **Xxx** — `MagicComposeActiveRecordTrait` + composed traits + utility methods

```php
use Yiisoft\ActiveRecord\ActiveRecord;
use Yiisoft\ActiveRecord\Trait\MagicPropertiesTrait;

class BaseProduct extends ActiveRecord {
    use MagicPropertiesTrait;

    protected int $id;
    protected string $name = '';

    public function tableName(): string {
        return '{{%products}}';
    }
}
```

```php
use Blackcube\MagicCompose\MagicComposeActiveRecordTrait;

class Product extends BaseProduct {
    use MagicComposeActiveRecordTrait, ElasticTrait, HazeltreeTrait;
}
```

`MagicComposeActiveRecordTrait` **must** be in the child class, separated from native AR traits in the base class. This avoids `__get`/`__set` conflicts between native AR magic and MagicCompose dispatch.

### Multiple composed traits

Multiple traits with `#[MagicExtend]` handlers on the same AR method are chained by priority:

```php
// Priority::HIGH runs first
// Chain: HighTrait:before → NormalTrait:before → parent → NormalTrait:after → HighTrait:after
```

Each trait calls `$this->next(...)` to continue the chain. The last handler in the chain calls the actual parent method.
