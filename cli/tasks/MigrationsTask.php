<?php

/*
 * Phalcon Ext
 * Copyright (c) 2016 David Hübner
 * This source file is subject to the New BSD License
 * Licence is bundled with this package in the file docs/LICENSE.txt
 * Author: David Hübner <david.hubner@gmail.com>
 */

use Phalcon\Cli\Task;

/**
 * PhalconExt console migrations task
 *
 * Usage via. your console script (here console.php):
 *
 * php console.php migrations generate
 * php console.php migrations migrate
 * php console.php migrations migrate 160617133459
 *
 * You need to register migrations service to your DI
 *
 * <code>
 * $this->getDI()->set('migrations', function () {
 *     $migrationsDir = '...';
 *     return new \PhalconExt\Db\SqlMigrations($this->get('db'), $migrationsDir);
 * }, true);
 * </code>
 *
 * @author     David Hübner <david.hubner at google.com>
 * @version    Release: @package_version@
 * @since      Release 1.1
 */
class MigrationsTask extends Task
{

    /**
     * Migrates database to last or selected version
     * @param array $args - default null
     */
    public function migrateAction(Array $args = null)
    {
        $migrations = $this->getDI()->get('migrations');
        $migrations->migrate((isset($args[0]) ? $args[0] : null));
    }

    /**
     * Outputs all SQL statements for migrating database to last or selected version
     * @param array $args - default null
     */
    public function sqlAction(Array $args = null)
    {
        $migrations = $this->getDI()->get('migrations');
        $migrations->sql((isset($args[0]) ? $args[0] : null));
    }

    /**
     * Generates new migration class
     */
    public function generateAction()
    {
        $migrations = $this->getDI()->get('migrations');
        $migrations->generate();
    }
}
