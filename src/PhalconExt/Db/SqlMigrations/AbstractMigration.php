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
 * @since      Release 1.1
 */
abstract class AbstractMigration
{

    /**
     * @var array - sql statements to execute
     */
    protected $statements = [];

    /**
     * @var array - statement on corresponding index is routine
     */
    protected $routines = [];

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
     * Returns if SQL statement on corresponding index is routine
     * @param  int $index
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function isRoutine($index)
    {
        if (isset($this->routines[$index])) {
            return $this->routines[$index];
        }

        throw new \InvalidArgumentException('Wrong index');
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
     * @param  string $statement - SQL statement
     * @param  bool $routine - statement is routine like TRIGGER etc., default false
     * @return \PhalconExt\Db\SqlMigrations\AbstractMigration
     */
    protected function addSql($statement, $routine = false)
    {
        $this->statements[] = $statement;
        $this->routines[] = $routine;
        return $this;
    }
}
