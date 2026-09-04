<?php

declare(strict_types=1);

/**
 * MagicNotHandledException.php
 *
 * PHP Version 8.4
 *
 * @author Philippe Gaultier <philippe@blackcube.io>
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 */

namespace Blackcube\MagicCompose\Exceptions;

/**
 * Exception thrown by magic handlers to signal they don't handle the property/method.
 * Lightweight, no message required.
 *
 * @author Philippe Gaultier <philippe@blackcube.io>
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 */
class MagicNotHandledException extends \Exception
{
}
