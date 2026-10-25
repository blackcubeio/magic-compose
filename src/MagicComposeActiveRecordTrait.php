<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\MagicCompose;

use Yiisoft\ActiveRecord\ActiveRecordInterface;

/**
 * Trait for composing magic methods and AR overrides in Yii3 ActiveRecord.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
trait MagicComposeActiveRecordTrait
{
    use MagicComposeTrait;
    use MagicComposeMethodsBaseTrait;

    protected function propertyValuesInternal(): array
    {
        return $this->executeExtendChain(
            'propertyValuesInternal',
            fn() => parent::propertyValuesInternal(),
            []
        );
    }

    protected function refreshInternal(array|ActiveRecordInterface|null $record = null): bool
    {
        return $this->executeExtendChain(
            'refreshInternal',
            fn($r) => parent::refreshInternal($r),
            [$record]
        );
    }

    protected function populateProperty(string $name, mixed $value): void
    {
        $this->executeExtendChain(
            'populateProperty',
            fn($n, $v) => parent::populateProperty($n, $v),
            [
                $name,
                $value,
            ]
        );
    }

    protected function deleteInternal(): int
    {
        return $this->executeExtendChain(
            'deleteInternal',
            fn() => parent::deleteInternal(),
            []
        );
    }

    public function populateRecord(array|object $row): static
    {
        return $this->executeExtendChain(
            'populateRecord',
            fn($r) => parent::populateRecord($r),
            [$row]
        );
    }
}
