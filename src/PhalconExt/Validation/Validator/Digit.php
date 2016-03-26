<?php

/*
 * Phalcon Ext
 * Copyright (c) 2016 David Hübner
 * This source file is subject to the New BSD License
 * Licence is bundled with this package in the file docs/LICENSE.txt
 * Author: David Hübner <david.hubner@gmail.com>
 */

namespace PhalconExt\Validation\Validator;

use Phalcon\Validation;

/**
 * Validates if value is whole number
 * Can be set as string or integer 
 *
 * Usage:
 * 
 * new \PhalconExt\Validation\Validator\Digit([
 *     'message' => {string - validation message},
 *     'allowEmpty' => {bool - allow empty value}
 * ])
 *
 * @author     David Hübner <david.hubner at google.com>
 * @version    Release: @package_version@
 * @since      Release 1.0
 */
class Digit extends Validation\Validator
{

    /**
     * Value validation
     *
     * @author  David Hübner <david.hubner at google.com>
     * @param   \Phalcon\Validation $validation - validation object
     * @param   string $attribute - validated attribute
     * @return  bool
     */
    public function validate(Validation $validation, $attribute)
    {
        $allowEmpty = $this->getOption('allowEmpty');
        $value = $validation->getValue($attribute);

        if ($allowEmpty && ((is_scalar($value) && (string) $value === '') || is_null($value))) {
            return true;
        }

        if (is_int($value) || ctype_digit($value)) {
            return true;
        }

        $message = ($this->hasOption('message') ? $this->getOption('message') : 'Not digit');

        $validation->appendMessage(
            new Validation\Message($message, $attribute, 'DigitValidator')
        );

        return false;
    }

}
