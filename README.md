# Phalcon Ext

Various extensions and utilities for Phalcon Framework

## Pre-requisites

1. [Phalcon framework 2.x or 3.x](https://phalconphp.com/)

## Installation

### Using Composer
You can use the `composer` package manager to install. Either run:

    $ php composer.phar require davihu/phalcon-ext "^1.2"

or add:

    "davihu/phalcon-ext": "^1.2"

to your composer.json file

## Whats new?

### Version 1.2

#### Added SQL database migrations support

Can be easily attached to your console application.

##### Supported commands:

    php console.php migrations generate                        # generates new migration class
    php console.php migrations migrate                         # migrates database to last version
    php console.php migrations migrate 160910160944            # migrates database to selected version
    php console.php migrations sql > migrate.sql               # saves SQL statements for migration to last version to file migrate.sql
    php console.php migrations sql 160910160944 > migrate.sql  # saves SQL statements for migration to selected version to file migrate.sql

##### Set up in console bootstrap file:

1) Choose migrations directory

    define('MIGRATIONS_DIR', '... your migrations dir ...');

2) Register dir to your loader

Without namespace usage

    $loader->registerDirs([ ... , MIGRATIONS_DIR]);

With namespace usage

    $loader->registerNamespaces([ ... , 'Your\\Namespace' => MIGRATIONS_DIR]);

3) Register migrations service to DI

Without namespace usage

    $di->set('migrations', function () {
        return new \PhalconExt\Db\SqlMigrations($this->get('db'), MIGRATIONS_DIR);
    }, true);

With namespace usage

    $di->set('migrations', function () {
        return new \PhalconExt\Db\SqlMigrations($this->get('db'), MIGRATIONS_DIR, 'Your\\Namespace');
    }, true);

##### Writing migrations classes

    use PhalconExt\Db\SqlMigrations\AbstractMigration;

    class Migration160910160944 extends AbstractMigration
    {
        public function up()
        {
            # simple statements
            $this->addSql("ALTER TABLE robots ADD COLUMN number VARCHAR(20)");

            # routine statements like triggers, procedures and functions
            $this->addSql("CREATE TRIGGER MyTrigger ...", true);
        }

        public function down()
        {
            $this->addSql("ALTER TABLE robots DROP COLUMN number");
            $this->addSql("DROP TRIGGER MyTrigger");
        }
    }

Thats all, very simple but powerfull!

## Contents

### Db
* [PhalconExt\Mvc\Db\SqlMigrations](src/PhalconExt/Db/SqlMigrations) - Database migrations service directly via. SQL statements
* [PhalconExt\Mvc\Db\SqlMigrations\AbstractMigration](src/PhalconExt/Db/SqlMigrations/AbstractMigration) - Abstract SQL migration, all migrations must extend this class

### Model
* [PhalconExt\Mvc\Model\Traits\RateLimitAccessTrait](src/PhalconExt/Mvc/Model) - Adds access rate limit support to target model
* [PhalconExt\Mvc\Model\Traits\RateLimitLoginTrait](src/PhalconExt/Mvc/Model) - Adds login rate limit support to target model

### Validators
* [PhalconExt\Validation\Validator\Color](src/PhalconExt/Validation/Validator) - Validates if value is valid color
* [PhalconExt\Validation\Validator\EmailDomain](src/PhalconExt/Validation/Validator) - Validates email domain existence via DNS
* [PhalconExt\Validation\Validator\PasswordRetype](src/PhalconExt/Validation/Validator) - Validates if password confirmation matches password
* [PhalconExt\Validation\Validator\PasswordStrength](src/PhalconExt/Validation/Validator) - Validates password strength
* [PhalconExt\Validation\Validator\StringLengthExact](src/PhalconExt/Validation/Validator) - Validates exact string length

## License

Phalcon Ext is open-sourced software licensed under the [New BSD License](docs/LICENSE.md). © David Hübner