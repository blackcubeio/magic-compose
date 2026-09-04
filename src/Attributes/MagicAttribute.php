<?php

declare(strict_types=1);

/**
 * MagicAttribute.php
 *
 * PHP Version 8.4
 *
 * @author Philippe Gaultier <philippe@blackcube.io>
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 */

namespace Blackcube\MagicCompose\Attributes;

/**
 * Base class for all magic method attributes.
 *
 * @author Philippe Gaultier <philippe@blackcube.io>
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 */
abstract class MagicAttribute
{
    public function __construct(
        public int $priority = Priority::NORMAL
    ) {
    }
}
