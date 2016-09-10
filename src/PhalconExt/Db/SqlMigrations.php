<?php

/*
 * Phalcon Ext
 * Copyright (c) 2016 David Hübner
 * This source file is subject to the New BSD License
 * Licence is bundled with this package in the file docs/LICENSE.txt
 * Author: David Hübner <david.hubner@gmail.com>
 */

namespace PhalconExt\Db;

use Phalcon\Db\AdapterInterface;

/**
 * SQL migrations for Phalcon
 *
 * @author     David Hübner <david.hubner at google.com>
 * @version    Release: @package_version@
 * @since      Release 2.0
 */
class SqlMigrations
{

    const DEFAULT_TABLE = 'Sql_Migrations';

    /**
     * @var \Phalcon\Db\AdapterInterface - DB adapter
     */
    protected $dbAdapter;

    /**
     * @var string - migrations directory
     */
    protected $dir;

    /**
     * @var string - migrations table
     */
    protected $table;

    /**
     * @var string - migrations namespace
     */
    protected $ns = '';

    /**
     * Creates new sql migrations
     * @param \Phalcon\Db\AdapterInterface $dbAdapter
     * @param string $dir
     * @param string $table - default 'Sql_Migrations'
     * @param string $ns - default ''
     */
    public function construct(AdapterInterface $dbAdapter, $dir, $table = self::DEFAULT_TABLE, $ns = '')
    {
        $this->dbAdapter = $dbAdapter;
        $this->setDir($dir);
        $this->setTable($table);
        $this->setNs($ns);
    }

    /**
     * Gets migrations directory
     * @return string
     */
    public function getDir()
    {
        return $this->dir;
    }

    /**
     * Sets migrations directory (where migration classes are stored)
     * @param  string $dir
     * @return \PhalconExt\Db\SqlMigrations
     * @throws \InvalidArgumentException
     */
    public function setDir($dir)
    {
        if (!is_dir($dir)) {
            throw new \InvalidArgumentException('Directory does not exist');
        }

        $this->dir = $dir;

        return $this;
    }

    /**
     * Gets migrations table
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Sets migrations table (where processed migrations are stored)
     * @param  string $table
     * @return \PhalconExt\Db\SqlMigrations
     * @throws \InvalidArgumentException
     */
    public function setTable($table)
    {
        if (empty($table)) {
            throw new \InvalidArgumentException('Table name must not be empty');
        }

        $this->table = $table;

        return $this;
    }

    /**
     * Gets migrations namespace
     * @return string
     */
    public function getNs()
    {
        return $this->ns;
    }

    /**
     * Sets migrations namespace
     * @param  string $ns
     * @return \PhalconExt\Db\SqlMigrations
     */
    public function setNs($ns)
    {
        $this->ns = $ns;
        return $this;
    }

    /**
     * Migrates database to last or selected version
     * @param  string $version
     */
    public function migrate($version = null)
    {

    }

    /**
     * Generates new migration class and returns its name
     * @return string
     * @throws \BadMethodCallException
     */
    public function generate()
    {
        if (empty($this->dir)) {
            throw new \BadMethodCallException('Migration directory is not set, use method setDir($dir) to set it');
        }

        if (!is_writable($this->dir)) {
            throw new \BadMethodCallException('Migration directory ' . $this->dir . ' is not writeable');
        }

        $name = 'Migration' . date('ymdHis');

        $m = '<?php' . PHP_EOL . PHP_EOL;

        if ($this->ns) {
            $m .= 'namespace ' . $this->ns . ';' . PHP_EOL . PHP_EOL;
        }

        $m .= 'use PhalconExt\Db\SqlMigrations\AbstractMigration;' . PHP_EOL . PHP_EOL
            . 'class ' . $name . ' extends AbstractMigration' . PHP_EOL
            . '{' . PHP_EOL
            . '    public function up()' . PHP_EOL
            . '    {' . PHP_EOL
            . '        $this->addSql("");' . PHP_EOL
            . '    }' . PHP_EOL . PHP_EOL
            . '    public function down()' . PHP_EOL
            . '    {' . PHP_EOL
            . '        $this->addSql("");' . PHP_EOL
            . '    }' . PHP_EOL
            . '}' . PHP_EOL;

        $handle = fopen(rtrim(DIRECTORY_SEPARATOR, $this->dir) . DIRECTORY_SEPARATOR . $name . '.php', 'w');

        if (empty($handle)) {
            throw new \RuntimeException('Could not create migration file');
        }

        fwrite($handle, $m);
        fclose($handle);

        return $name;
    }
}
