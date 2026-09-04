<?php

declare(strict_types=1);

/**
 * ParentWithMagic.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\MagicCompose\Tests\Support;

/**
 * Parent class with magic methods for testing fallback behavior.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class ParentWithMagic
{
    protected array $parentData = [];
    public array $parentCallLog = [];

    public function __get(string $name): mixed
    {
        $this->parentCallLog[] = 'parent::__get('.$name.')';
        return $this->parentData[$name] ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->parentCallLog[] = 'parent::__set('.$name.')';
        $this->parentData[$name] = $value;
    }

    public function __isset(string $name): bool
    {
        $this->parentCallLog[] = 'parent::__isset('.$name.')';
        return isset($this->parentData[$name]) === true;
    }

    public function __unset(string $name): void
    {
        $this->parentCallLog[] = 'parent::__unset('.$name.')';
        unset($this->parentData[$name]);
    }

    public function __call(string $name, array $arguments): mixed
    {
        $this->parentCallLog[] = 'parent::__call('.$name.')';
        return 'parent-'.$name;
    }
}
