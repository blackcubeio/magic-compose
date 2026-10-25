<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\MagicCompose\Attributes;

use Attribute;

/**
 * Marks a method as a __call handler.
 * Expected signature: handler(string $name, array $arguments): mixed
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
#[Attribute(Attribute::TARGET_METHOD)]
class MagicCall extends MagicAttribute
{
}
