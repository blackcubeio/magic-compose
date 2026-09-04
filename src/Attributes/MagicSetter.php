<?php

declare(strict_types=1);

/**
 * MagicSetter.php
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
 * Marks a method as a __set handler.
 * Expected signature: handler(string $name, mixed $value): void
 *
 * @author Philippe Gaultier <philippe@blackcube.io>
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 */
#[Attribute(Attribute::TARGET_METHOD)]
class MagicSetter extends MagicAttribute
{
}
