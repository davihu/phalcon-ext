<?php

/*
 * Phalcon Ext
 * Copyright (c) 2016 David Hübner
 * This source file is subject to the New BSD License
 * Licence is bundled with this package in the file docs/LICENSE.txt
 * Author: David Hübner <david.hubner@gmail.com>
 */

namespace PhalconExt\Validation\Validator;

use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\AbstractValidator;
use Phalcon\Filter\Validation\Exception;
use Phalcon\Messages\Message;

/**
 * Validates if password retype matches password
 *
 * <code>
 * new \PhalconExt\Validation\Validator\PasswordRetype([
 *     'origField' => {string - original field attribute},
 *     'message' => {string - validation message},
 *     'allowEmpty' => {bool - allow empty value}
 * ])
 * </code>
 *
 * @author     David Hübner <david.hubner at google.com>
 * @version    Release: @package_version@
 * @since      Release 1.0
 */
class PasswordRetype extends AbstractValidator
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
    public function validate(Validation $validation, $attribute): bool
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

        $validation->appendMessage(new Message($message, $attribute, 'PasswordRetypeValidator'));

        return false;
    }

}
