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
 * Validates exact string length
 *
 * <code>
 * new \PhalconExt\Validation\Validator\StringLengthExact([
 *     'message' => {string - validation message},
 *     'allowEmpty' => {bool - allow empty value}
 * ])
 * </code>
 *
 * @author     David Hübner <david.hubner at google.com>
 * @version    Release: @package_version@
 * @since      Release 1.0
 */
class StringLengthExact extends AbstractValidator
{

    /**
     * Value validation
     *
     * @author  David Hübner <david.hubner at google.com>
     * @param   \Phalcon\Validation $validation - validation object
     * @param   string $attribute - validated attribute
     * @return  bool
     * @throws  Exception
     */
    public function validate(Validation $validation, $attribute): bool
    {
        if (!$this->hasOption('length')) {
            throw new Exception('Length must be set');
        }

        $allowEmpty = $this->getOption('allowEmpty');
        $value = $validation->getValue($attribute);

        if ($allowEmpty && ((is_scalar($value) && (string) $value === '') || is_null($value))) {
            return true;
        }

        $length = $this->getOption('length');

        if ((is_string($value) || is_numeric($value)) && mb_strlen($value) == $length) {
            return true;
        }

        $message = ($this->hasOption('message') ? $this->getOption('message') : 'Length must be ' . $length);

        $validation->appendMessage(new Message($message, $attribute, 'StringLengthExactValidator'));

        return false;
    }

}
