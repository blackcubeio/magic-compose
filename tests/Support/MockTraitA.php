<?php

declare(strict_types=1);

/**
 * MockTraitA.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\MagicCompose\Tests\Support;

use Blackcube\MagicCompose\Attributes\MagicExtend;
use Blackcube\MagicCompose\Attributes\MagicGetter;
use Blackcube\MagicCompose\Attributes\MagicSetter;
use Blackcube\MagicCompose\Attributes\Priority;
use Blackcube\MagicCompose\Exceptions\MagicNotHandledException;

/**
 * Mock trait with Priority::HIGH for testing priority ordering.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
trait MockTraitA
{
    private ?string $traitAValue = null;
    private array $traitALog = [];

    #[MagicGetter(Priority::HIGH)]
    protected function traitAGet(string $name): mixed
    {
        if ($name === 'traitAValue') {
            return $this->traitAValue;
        }
        if ($name === 'traitALog') {
            return $this->traitALog;
        }
        throw new MagicNotHandledException();
    }

    #[MagicSetter(Priority::HIGH)]
    protected function traitASet(string $name, mixed $value): void
    {
        if ($name === 'traitAValue') {
            $this->traitAValue = $value;
            return;
        }
        throw new MagicNotHandledException();
    }

    #[MagicExtend('propertyValuesInternal', Priority::HIGH)]
    protected function traitAPropertyValues(): array
    {
        $this->traitALog[] = 'traitAPropertyValues:before';
        $values = $this->next();
        $this->traitALog[] = 'traitAPropertyValues:after';
        $values['traitAValue'] = $this->traitAValue;
        return $values;
    }

    #[MagicExtend('populateRecord', Priority::HIGH)]
    protected function traitAPopulateRecord(array|object $row): static
    {
        $this->traitALog[] = 'traitAPopulateRecord:before';
        $result = $this->next($row);
        $this->traitALog[] = 'traitAPopulateRecord:after';
        return $result;
    }

    #[MagicExtend('populateProperty', Priority::HIGH)]
    protected function traitAPopulateProperty(string $name, mixed $value): void
    {
        $this->traitALog[] = 'traitAPopulateProperty:'.$name;
        if ($name === 'traitAValue') {
            $this->traitAValue = $value;
            return;
        }
        $this->next($name, $value);
    }

    #[MagicExtend('refreshInternal', Priority::HIGH)]
    protected function traitARefreshInternal(array|\Yiisoft\ActiveRecord\ActiveRecordInterface|null $record): bool
    {
        $this->traitALog[] = 'traitARefreshInternal:before';
        $result = $this->next($record);
        $this->traitALog[] = 'traitARefreshInternal:after';
        return $result;
    }

    #[MagicExtend('deleteInternal', Priority::HIGH)]
    protected function traitADeleteInternal(): int
    {
        $this->traitALog[] = 'traitADeleteInternal:before';
        $result = $this->next();
        $this->traitALog[] = 'traitADeleteInternal:after';
        return $result;
    }

    public function clearTraitALog(): void
    {
        $this->traitALog = [];
    }
}
