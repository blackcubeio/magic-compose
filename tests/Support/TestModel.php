<?php

declare(strict_types=1);

/**
 * TestModel.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\MagicCompose\Tests\Support;

use Blackcube\MagicCompose\Attributes\MagicCall;
use Blackcube\MagicCompose\Attributes\MagicGetter;
use Blackcube\MagicCompose\Attributes\MagicIsset;
use Blackcube\MagicCompose\Attributes\MagicSetter;
use Blackcube\MagicCompose\Attributes\MagicUnset;
use Blackcube\MagicCompose\Attributes\Priority;
use Blackcube\MagicCompose\Exceptions\MagicNotHandledException;
use Blackcube\MagicCompose\MagicComposeTrait;

/**
 * Test model with all magic method handler combinations.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class TestModel
{
    use MagicComposeTrait;

    private string $virtualProperty = 'initial';
    private array $data = [];
    public array $callLog = [];

    #[MagicGetter(Priority::HIGH)]
    protected function getHighPriority(string $name): mixed
    {
        if ($name === 'highPriorityProp') {
            $this->callLog[] = 'getHighPriority';
            return 'high';
        }
        throw new MagicNotHandledException();
    }

    #[MagicGetter(Priority::NORMAL)]
    protected function getNormalPriority(string $name): mixed
    {
        if ($name === 'normalPriorityProp') {
            $this->callLog[] = 'getNormalPriority';
            return 'normal';
        }
        if ($name === 'highPriorityProp') {
            $this->callLog[] = 'getNormalPriority-shouldNotBeCalled';
            return 'normal-wrong';
        }
        throw new MagicNotHandledException();
    }

    #[MagicGetter(Priority::LOW)]
    protected function getLowPriority(string $name): mixed
    {
        if ($name === 'lowPriorityProp') {
            $this->callLog[] = 'getLowPriority';
            return 'low';
        }
        throw new MagicNotHandledException();
    }

    #[MagicGetter]
    protected function getVirtual(string $name): mixed
    {
        if ($name === 'virtualProperty') {
            $this->callLog[] = 'getVirtual';
            return $this->virtualProperty;
        }
        throw new MagicNotHandledException();
    }

    #[MagicSetter(Priority::HIGH)]
    protected function setHighPriority(string $name, mixed $value): void
    {
        if ($name === 'highPriorityProp') {
            $this->callLog[] = 'setHighPriority';
            $this->data[$name] = $value;
            return;
        }
        throw new MagicNotHandledException();
    }

    #[MagicSetter]
    protected function setVirtual(string $name, mixed $value): void
    {
        if ($name === 'virtualProperty') {
            $this->callLog[] = 'setVirtual';
            $this->virtualProperty = $value;
            return;
        }
        throw new MagicNotHandledException();
    }

    #[MagicIsset(Priority::HIGH)]
    protected function issetHighPriority(string $name): bool
    {
        if ($name === 'highPriorityProp') {
            $this->callLog[] = 'issetHighPriority';
            return isset($this->data[$name]) === true;
        }
        throw new MagicNotHandledException();
    }

    #[MagicIsset]
    protected function issetVirtual(string $name): bool
    {
        if ($name === 'virtualProperty') {
            $this->callLog[] = 'issetVirtual';
            return true;
        }
        if ($name === 'undefinedProperty') {
            $this->callLog[] = 'issetUndefined';
            return false;
        }
        throw new MagicNotHandledException();
    }

    #[MagicUnset(Priority::HIGH)]
    protected function unsetHighPriority(string $name): void
    {
        if ($name === 'highPriorityProp') {
            $this->callLog[] = 'unsetHighPriority';
            unset($this->data[$name]);
            return;
        }
        throw new MagicNotHandledException();
    }

    #[MagicUnset]
    protected function unsetVirtual(string $name): void
    {
        if ($name === 'virtualProperty') {
            $this->callLog[] = 'unsetVirtual';
            $this->virtualProperty = '';
            return;
        }
        throw new MagicNotHandledException();
    }

    #[MagicCall(Priority::HIGH)]
    protected function callHighPriority(string $name, array $arguments): mixed
    {
        if ($name === 'highPriorityMethod') {
            $this->callLog[] = 'callHighPriority';
            return 'high-'.implode('-', $arguments);
        }
        throw new MagicNotHandledException();
    }

    #[MagicCall]
    protected function callVirtual(string $name, array $arguments): mixed
    {
        if ($name === 'virtualMethod') {
            $this->callLog[] = 'callVirtual';
            return 'virtual-'.implode('-', $arguments);
        }
        throw new MagicNotHandledException();
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function clearCallLog(): void
    {
        $this->callLog = [];
    }
}
