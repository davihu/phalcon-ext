# Phalcon Ext

Various extensions and utilities for Phalcon Framework

## Pre-requisites

1. [Phalcon framework 2.x or 3.0](https://phalconphp.com/)

## Installation

### Using Composer
You can use the `composer` package manager to install. Either run:

    $ php composer.phar require davihu/phalcon-ext "dev-master"

or add:

    "davihu/phalcon-ext": "^1.0"

to your composer.json file

## Contents

### Model
* [PhalconExt\Mvc\Model\Traits\RateLimitAccessTrait](src/PhalconExt/Mvc/Model) - Adds access rate limit support to target model
* [PhalconExt\Mvc\Model\Traits\RateLimitLoginTrait](src/PhalconExt/Mvc/Model) - Adds login rate limit support to target model

### Validators
* [PhalconExt\Validation\Validator\Color](src/PhalconExt/Validation/Validator) - Validates if value is valid color
* [PhalconExt\Validation\Validator\Digit](src/PhalconExt/Validation/Validator) - Validates if value is whole number, can be set as string or integer
* [PhalconExt\Validation\Validator\EmailDomain](src/PhalconExt/Validation/Validator) - Validates email domain existence via DNS
* [PhalconExt\Validation\Validator\PasswordRetype](src/PhalconExt/Validation/Validator) - Validates if password confirmation matches password
* [PhalconExt\Validation\Validator\PasswordStrength](src/PhalconExt/Validation/Validator) - Validates password strength
* [PhalconExt\Validation\Validator\StringLengthExact](src/PhalconExt/Validation/Validator) - Validates exact string length

## License

Phalcon Ext is open-sourced software licensed under the [New BSD License](docs/LICENSE.md). © David Hübner