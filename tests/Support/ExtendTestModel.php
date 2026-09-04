<?php

declare(strict_types=1);

/**
 * ExtendTestModel.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\MagicCompose\Tests\Support;

use Blackcube\MagicCompose\MagicComposeActiveRecordTrait;
use Yiisoft\ActiveRecord\ActiveRecord;

/**
 * Test model extending ActiveRecord with MockTraitA and MockTraitB.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class ExtendTestModel extends ActiveRecord
{
    use MagicComposeActiveRecordTrait, MockTraitA, MockTraitB;

    protected int $id;
    protected string $name = '';

    public function tableName(): string
    {
        return 'extendTestModels';
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
