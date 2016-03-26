<?php

/*
 * Phalcon Ext
 * Copyright (c) 2016 David Hübner
 * This source file is subject to the New BSD License
 * Licence is bundled with this package in the file docs/LICENSE.txt
 * Author: David Hübner <david.hubner@gmail.com>
 */

namespace PhalconExt\Validation\Validator;

use Phalcon\Validation,
    Phalcon\Validation\Exception;

/**
 * Validates if password retype matches password
 * 
 * Usage:
 * 
 * new PasswordRetype([
 *     'message' => 'Passwords do not match',
 *     'origField' => 'password'
 * ])
 *
 * @author     David Hübner <david.hubner at google.com>
 * @version    Release: @package_version@
 * @since      Release 1.0
 */
class PasswordRetype extends Validation\Validator
{

    /**
     * Value validation
     *
     * @author  David Hübner <david.hubner at google.com>
     * @param   \Phalcon\Validation $validation - validation object
     * @param   string $attribute - validated attribute
     * @return  bool
     * @throws  \Phalcon\Validation\Exception
     */
    public function validate(Validation $validation, $attribute)
    {
        if (!$this->hasOption('origField')) {
            throw new Exception('Original field must be set');
        }

        $allowEmpty = $this->getOption('allowEmpty');
        $value = $validation->getValue($attribute);

        if ($allowEmpty && ((is_scalar($value) && (string) $value === '') || is_null($value))) {
            return true;
        }

        $origField = $this->getOption('origField');
        $origValue = $validation->getValue($origField);

        if (is_string($value) && $value == $origValue) {
            return true;
        }

        $message = ($this->hasOption('message') ? $this->getOption('message') : 'Passwords do not match');

        $validation->appendMessage(
            new Validation\Message($message, $attribute, 'PasswordRetypeValidator')
        );

        return false;
    }

}
