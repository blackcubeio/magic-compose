<?php

declare(strict_types=1);

/**
 * ChildModelWithParent.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\MagicCompose\Tests\Support;

use Blackcube\MagicCompose\Attributes\MagicGetter;
use Blackcube\MagicCompose\Attributes\MagicSetter;
use Blackcube\MagicCompose\Exceptions\MagicNotHandledException;
use Blackcube\MagicCompose\MagicComposeTrait;

/**
 * Child model that extends ParentWithMagic to test fallback to parent.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class ChildModelWithParent extends ParentWithMagic
{
    use MagicComposeTrait;

    public array $childCallLog = [];

    #[MagicGetter]
    protected function getChildProperty(string $name): mixed
    {
        if ($name === 'childProp') {
            $this->childCallLog[] = 'getChildProperty';
            return 'child-value';
        }
        throw new MagicNotHandledException();
    }

    #[MagicSetter]
    protected function setChildProperty(string $name, mixed $value): void
    {
        if ($name === 'childProp') {
            $this->childCallLog[] = 'setChildProperty';
            return;
        }
        throw new MagicNotHandledException();
    }
}
