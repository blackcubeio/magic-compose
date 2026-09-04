<?php

declare(strict_types=1);

/**
 * Priority.php
 *
 * PHP Version 8.4
 *
 * @author Philippe Gaultier <philippe@blackcube.io>
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 */

namespace Blackcube\MagicCompose\Attributes;

/**
 * Priority constants for magic method handlers.
 *
 * @author Philippe Gaultier <philippe@blackcube.io>
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 */
class Priority
{
    public const LOW = 10;
    public const NORMAL = 50;
    public const HIGH = 90;
    public const CRITICAL = 100;
}
