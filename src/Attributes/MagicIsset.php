<?php

declare(strict_types=1);

/**
 * MagicIsset.php
 *
 * PHP Version 8.4
 *
 * @author Philippe Gaultier <philippe@blackcube.io>
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 */

namespace Blackcube\MagicCompose\Attributes;

use Attribute;

/**
 * Marks a method as a __isset handler.
 * Expected signature: handler(string $name): bool
 *
 * @author Philippe Gaultier <philippe@blackcube.io>
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 */
#[Attribute(Attribute::TARGET_METHOD)]
class MagicIsset extends MagicAttribute
{
}
