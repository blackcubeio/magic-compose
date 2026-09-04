<?php

declare(strict_types=1);

/**
 * MagicCall.php
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
 * Marks a method as a __call handler.
 * Expected signature: handler(string $name, array $arguments): mixed
 *
 * @author Philippe Gaultier <philippe@blackcube.io>
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 */
#[Attribute(Attribute::TARGET_METHOD)]
class MagicCall extends MagicAttribute
{
}
