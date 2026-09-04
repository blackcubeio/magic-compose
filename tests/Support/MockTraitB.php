<?php

declare(strict_types=1);

/**
 * MockTraitB.php
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
 * Mock trait with Priority::NORMAL for testing priority ordering.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
trait MockTraitB
{
    private ?string $traitBValue = null;
    private array $traitBLog = [];

    #[MagicGetter(Priority::NORMAL)]
    protected function traitBGet(string $name): mixed
    {
        if ($name === 'traitBValue') {
            return $this->traitBValue;
        }
        if ($name === 'traitBLog') {
            return $this->traitBLog;
        }
        throw new MagicNotHandledException();
    }

    #[MagicSetter(Priority::NORMAL)]
    protected function traitBSet(string $name, mixed $value): void
    {
        if ($name === 'traitBValue') {
            $this->traitBValue = $value;
            return;
        }
        throw new MagicNotHandledException();
    }

    #[MagicExtend('propertyValuesInternal', Priority::NORMAL)]
    protected function traitBPropertyValues(): array
    {
        $this->traitBLog[] = 'traitBPropertyValues:before';
        $values = $this->next();
        $this->traitBLog[] = 'traitBPropertyValues:after';
        $values['traitBValue'] = $this->traitBValue;
        return $values;
    }

    #[MagicExtend('populateRecord', Priority::NORMAL)]
    protected function traitBPopulateRecord(array|object $row): static
    {
        $this->traitBLog[] = 'traitBPopulateRecord:before';
        $result = $this->next($row);
        $this->traitBLog[] = 'traitBPopulateRecord:after';
        return $result;
    }

    #[MagicExtend('populateProperty', Priority::NORMAL)]
    protected function traitBPopulateProperty(string $name, mixed $value): void
    {
        $this->traitBLog[] = 'traitBPopulateProperty:'.$name;
        if ($name === 'traitBValue') {
            $this->traitBValue = $value;
            return;
        }
        $this->next($name, $value);
    }

    #[MagicExtend('refreshInternal', Priority::NORMAL)]
    protected function traitBRefreshInternal(array|\Yiisoft\ActiveRecord\ActiveRecordInterface|null $record): bool
    {
        $this->traitBLog[] = 'traitBRefreshInternal:before';
        $result = $this->next($record);
        $this->traitBLog[] = 'traitBRefreshInternal:after';
        return $result;
    }

    #[MagicExtend('deleteInternal', Priority::NORMAL)]
    protected function traitBDeleteInternal(): int
    {
        $this->traitBLog[] = 'traitBDeleteInternal:before';
        $result = $this->next();
        $this->traitBLog[] = 'traitBDeleteInternal:after';
        return $result;
    }

    public function clearTraitBLog(): void
    {
        $this->traitBLog = [];
    }
}
