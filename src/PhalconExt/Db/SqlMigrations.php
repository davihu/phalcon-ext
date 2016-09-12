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
use Phalcon\Db\Column;

/**
 * SQL migrations for Phalcon
 *
 * @author     David Hübner <david.hubner at google.com>
 * @version    Release: @package_version@
 * @since      Release 1.1
 */
class SqlMigrations
{

    const DEFAULT_TABLE = 'Sql_Migrations';
    const CLASS_PREFIX = 'Migration';

    /**
     * @var \Phalcon\Db\AdapterInterface - DB adapter
     */
    protected $dbAdapter;

    /**
     * @var array - list of processed migrations
     */
    protected $processed;

    /**
     * @var string - current database version
     */
    protected $currentVersion = '000000000000';

    /**
     * @var array - list of available migrations
     */
    protected $available;

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
     * @param string $ns - default ''
     * @param string $table - default 'Sql_Migrations'
     */
    public function __construct(AdapterInterface $dbAdapter, $dir, $ns = '', $table = self::DEFAULT_TABLE)
    {
        $this->dbAdapter = $dbAdapter;
        $this->setDir($dir);
        $this->setNs($ns);
        $this->setTable($table);
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
     * Generates new migration class and outputs its filename
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

        $name = $this->version2class(date('ymdHis'), false);

        $m = '<?php' . PHP_EOL . PHP_EOL;

        if ($this->ns) {
            $m .= 'namespace ' . $this->ns . ';' . PHP_EOL . PHP_EOL;
        }

        $m .= 'use PhalconExt\Db\SqlMigrations\AbstractMigration;' . PHP_EOL . PHP_EOL
            . '/**' . PHP_EOL
            . ' * Auto generated migration' . PHP_EOL
            . ' * @author  PhalconExt' . PHP_EOL
            . ' * @see     https://github.com/davihu/phalcon-ext' . PHP_EOL
            . ' */' . PHP_EOL
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

        $filename = rtrim($this->dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $name . '.php';

        $handle = fopen($filename, 'w');

        if (empty($handle)) {
            throw new \RuntimeException('Could not create migration file');
        }

        fwrite($handle, $m);
        fclose($handle);

        echo 'Generated new migration file ' . $filename . PHP_EOL;
    }

    /**
     * Migrates database to last or selected version
     * @param  string $version
     */
    public function migrate($version = null)
    {
        if ($version && !preg_match('!^\d{12,12}$!', $version)) {
            throw new \InvalidArgumentException('Wrong version number ' . $version . ', expecting 12 digits number');
        }

        if (!$this->createTable()) {
            throw new \RuntimeException('Could not create migration table');
        }

        $this->initProcessed();
        $this->initAvailable();

        if (empty($this->available)) {
            echo 'No migrations available' . PHP_EOL;
            return;
        }

        if (empty($version)) {
            $version = end($this->available);
        }

        $done = 0;

        $done += $this->migrateUp($version);
        $done += $this->migrateDown($version);

        if (empty($done)) {
            echo 'No migrations to execute' . PHP_EOL;
        }
    }

    /**
     * Performs all migrations up to given max. version
     * @param  string $maxVersion
     * @return int
     * @throws \Exception
     */
    protected function migrateUp($maxVersion)
    {
        $done = 0;

        foreach ($this->available as $version) {
            if ($version > $maxVersion) {
                break;
            }

            if ($this->isProcessed($version)) {
                continue;
            }

            $className = $this->version2class($version);
            $migration = new $className();
            $migration->up();

            echo 'Migrating database UP to version ' . $version . PHP_EOL . PHP_EOL;

            try {
                $this->executeStatements($migration->getStatements());
            } catch (\Exception $ex) {
                echo PHP_EOL . 'MIGRATION ERROR - TERMINATING' . PHP_EOL . PHP_EOL;
                $migration->clearStatements();
                $migration->down();
                $this->executeStatements($migration->getStatements(), false);
                throw $ex;
            }

            echo PHP_EOL . 'SUCCESS database version moved UP to ' . $version . PHP_EOL . PHP_EOL;

            $this->setProcessed($version);
            ++$done;
        }

        return $done;
    }

    /**
     * Performs all migrations down to given max. version
     * @param  string $maxVersion
     * @return int
     * @throws \Exception
     */
    protected function migrateDown($maxVersion)
    {
        $done = 0;
        $reversed = array_reverse($this->available);

        foreach ($reversed as $version) {
            if ($version <= $maxVersion) {
                break;
            }

            if (!$this->isProcessed($version)) {
                continue;
            }

            $className = $this->version2class($version);
            $migration = new $className();
            $migration->down();

            echo 'Migrating database DOWN to version ' . $version . PHP_EOL . PHP_EOL;

            try {
                $this->executeStatements($migration->getStatements());
            } catch (\Exception $ex) {
                echo PHP_EOL . 'MIGRATION ERROR - TERMINATING' . PHP_EOL . PHP_EOL;
                throw $ex;
            }

            echo PHP_EOL . 'SUCCESS database version moved DOWN from ' . $version . PHP_EOL . PHP_EOL;

            $this->setUnprocessed($version);
            ++$done;
        }

        return $done;
    }

    /**
     * Outputs SQL statements to last or selected version
     * @param  string $version
     */
    public function sql($version = null)
    {
        if ($version && !preg_match('!^\d{12,12}$!', $version)) {
            throw new \InvalidArgumentException('Wrong version number ' . $version . ', expecting 12 digits number');
        }

        $this->initProcessed();
        $this->initAvailable();

        if (empty($this->available)) {
            echo '# No migrations available' . PHP_EOL;
            return;
        }

        if (empty($version)) {
            $version = end($this->available);
        }

        $done = 0;

        $done += $this->sqlUp($version);
        $done += $this->sqlDown($version);

        if (empty($done)) {
            echo '# No migrations to execute' . PHP_EOL;
        }
    }

    /**
     * Output all migrations statements up to given max. version
     * @param  string $maxVersion
     * @return int
     * @throws \Exception
     */
    protected function sqlUp($maxVersion)
    {
        $done = 0;

        foreach ($this->available as $version) {
            if ($version > $maxVersion) {
                break;
            }

            if ($this->isProcessed($version)) {
                continue;
            }

            $className = $this->version2class($version);
            $migration = new $className();
            $migration->up();

            echo '# Migrating database UP to version ' . $version . PHP_EOL;

            $statements = $migration->getStatements();

            foreach ($statements as $i => $statement) {
                if ($migration->isRoutine($i)) {
                    echo 'DELIMITER ;;' . PHP_EOL;
                    echo $statement . ';;' . PHP_EOL;
                    echo 'DELIMITER ;' . PHP_EOL;
                } else {
                    echo $statement . ';' . PHP_EOL;
                }
            }

            echo PHP_EOL;
            ++$done;
        }

        return $done;
    }

    /**
     * Output all migrations statements down to given max. version
     * @param  string $maxVersion
     * @return int
     * @throws \Exception
     */
    protected function sqlDown($maxVersion)
    {
        $done = 0;
        $reversed = array_reverse($this->available);

        foreach ($reversed as $version) {
            if ($version <= $maxVersion) {
                break;
            }

            if (!$this->isProcessed($version)) {
                continue;
            }

            $className = $this->version2class($version);
            $migration = new $className();
            $migration->down();

            echo '# Migrating database DOWN to version ' . $version . PHP_EOL;

            $statements = $migration->getStatements();

            foreach ($statements as $i => $statement) {
                if ($migration->isRoutine($i)) {
                    echo 'DELIMITER ;;' . PHP_EOL;
                    echo $statement . ';;' . PHP_EOL;
                    echo 'DELIMITER ;' . PHP_EOL;
                } else {
                    echo $statement . ';' . PHP_EOL;
                }
            }

            echo PHP_EOL;
            ++$done;
        }

        return $done;
    }

    /**
     * Executes migrations statements
     * @param array $statements
     * @param bool $bubble - bubble exception, default false
     */
    protected function executeStatements(Array $statements, $bubble = true)
    {
        foreach ($statements as $statement) {
            if ($bubble) {
                echo '    -> ' . $statement . PHP_EOL;
            }
            try {
                $this->dbAdapter->execute($statement);
            } catch (\Exception $ex) {
                if ($bubble) {
                    throw $ex;
                }
            }
        }
    }

    /**
     * Checks if migration table exists if not creates new
     * @return bool
     */
    protected function createTable()
    {
        if ($this->dbAdapter->tableExists($this->table)) {
            return true;
        }

        $result = $this->dbAdapter->createTable($this->table, null, [
            'columns' => [
                new Column(
                    'id', ['type' => Column::TYPE_CHAR, 'size' => 12, 'notNull' => true, 'primary' => true]
                )
            ]
        ]);

        return $result;
    }

    /**
     * Checks is migration version was processed
     * @param  string $version
     * @return bool
     */
    protected function isProcessed($version)
    {
        return (empty($this->processed[$version]) ? false : true);
    }

    /**
     * Sets migration version as processed
     * @param  string $version
     * @return bool
     */
    protected function setProcessed($version)
    {
        $this->processed[$version] = true;
        if ($version > $this->currentVersion) {
            $this->currentVersion = $version;
        }
        $this->dbAdapter->insert($this->table, [$version], ['id']);
    }

    /**
     * Sets migration version as unprocessed
     * @param  string $version
     * @return bool
     */
    protected function setUnprocessed($version)
    {
        unset($this->processed[$version]);
        end($this->processed);
        $this->currentVersion = key($this->processed);
        $this->dbAdapter->delete($this->table, 'id = ?', [$version]);
    }

    /**
     * Initializes processed migrations
     */
    protected function initProcessed()
    {
        $this->processed = [];
        $result = $this->dbAdapter->fetchAll('SELECT id FROM ' . $this->table . ' ORDER BY id');
        foreach ($result as $row) {
            $this->processed[$row['id']] = true;
            $this->currentVersion = $row['id'];
        }
    }

    /**
     * Initializes available migrations
     */
    protected function initAvailable()
    {
        $this->available = [];
        $files = scandir($this->dir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $this->available[] = $this->file2version($file);
        }
    }

    /**
     * Converts version ID to class name
     * @param  string $version
     * @param  bool $withNs
     * @return string
     */
    protected function version2class($version, $withNs = true)
    {
        return ($withNs ? $this->ns . '\\' : '') . self::CLASS_PREFIX . $version;
    }

    /**
     * Converts file name to version ID
     * @param  string $file
     * @return string
     */
    protected function file2version($file)
    {
        return str_replace([self::CLASS_PREFIX, '.php'], '', $file);
    }
}
