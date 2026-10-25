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
 * Marks a method as an override handler for parent class methods.
 * Handler signature must match the original method.
 * Use $this->next(...) to call the next handler in chain.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
#[Attribute(Attribute::TARGET_METHOD)]
class MagicExtend extends MagicAttribute
{
    public function __construct(
        public string $method,
        int $priority = Priority::NORMAL
    ) {
        parent::__construct($priority);
    }
}
