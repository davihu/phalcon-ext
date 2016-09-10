<?php

/*
 * Phalcon Ext
 * Copyright (c) 2016 David Hübner
 * This source file is subject to the New BSD License
 * Licence is bundled with this package in the file docs/LICENSE.txt
 * Author: David Hübner <david.hubner@gmail.com>
 */

namespace PhalconExt\Db\SqlMigrations;

/**
 * Abstract migration class
 *
 * @author     David Hübner <david.hubner at google.com>
 * @version    Release: @package_version@
 * @since      Release 2.0
 */
abstract class AbstractMigration
{

    /**
     * @var array - sql statements to execute
     */
    protected $statements = [];

    /**
     * Migration up
     */
    abstract public function up();

    /**
     * Migration down
     */
    abstract public function down();

    /**
     * Gets queued SQL statements
     * @return array
     */
    public function getStatements()
    {
        return $this->statements;
    }

    /**
     * Clears queued SQL statements
     * @return \PhalconExt\Db\SqlMigrations\AbstractMigration
     */
    public function clearStatements()
    {
        $this->statements = [];
        return $this;
    }

    /**
     * Adds SQL statement
     * @param  string $statement
     * @return \PhalconExt\Db\SqlMigrations\AbstractMigration
     */
    protected function addSql($statement)
    {
        $this->statements[] = $statement;
        return $this;
    }
}
