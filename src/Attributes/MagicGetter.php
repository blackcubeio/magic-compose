<?php

declare(strict_types=1);

/**
 * MagicGetter.php
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
 * Marks a method as a __get handler.
 * Expected signature: handler(string $name): mixed
 *
 * @author Philippe Gaultier <philippe@blackcube.io>
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 */
#[Attribute(Attribute::TARGET_METHOD)]
class MagicGetter extends MagicAttribute
{
}
