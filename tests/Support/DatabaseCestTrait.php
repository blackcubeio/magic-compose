<?php

declare(strict_types=1);

/**
 * DatabaseCestTrait.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\MagicCompose\Tests\Support;

use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Connection\ConnectionProvider;

/**
 * Trait for Cest classes that need database setup.
 *
 * Lifecycle per Cest:
 * 1. drop + create tables — once before the first test
 * 2. run all tests
 * 3. leave DB as-is
 */
trait DatabaseCestTrait
{
    protected ConnectionInterface $db;

    private static array $setupDone = [];

    public function _before(MagicExtendTester $I): void
    {
        $this->initializeDatabase();

        $className = static::class;
        if (isset(self::$setupDone[$className]) === false) {
            $this->createTables();
            self::$setupDone[$className] = true;
        }
    }

    private function initializeDatabase(): void
    {
        $helper = new MysqlHelper();
        $this->db = $helper->createConnection();
        ConnectionProvider::set($this->db);
    }

    private function createTables(): void
    {
        $this->db->createCommand('DROP TABLE IF EXISTS `extendTestModels`')->execute();
        $this->db->createCommand('
            CREATE TABLE `extendTestModels` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(255) NOT NULL,
                `traitAValue` VARCHAR(255) NULL,
                `traitBValue` VARCHAR(255) NULL
            )
        ')->execute();
    }
}
