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
use Phalcon\Messages\Message;

/**
 * Validates if value is valid color
 *
 * <code>
 * new \PhalconExt\Validation\Validator\Color([
 *     'message' => {string - validation message},
 *     'allowEmpty' => {bool - allow empty value}
 * ])
 * </code>
 *
 * @author     David Hübner <david.hubner at google.com>
 * @version    Release: @package_version@
 * @since      Release 1.0
 */
class Color extends AbstractValidator
{

    /**
     * Value validation
     *
     * @author  David Hübner <david.hubner at google.com>
     * @param   \Phalcon\Validation $validation - validation object
     * @param   string $attribute - validated attribute
     * @return  bool
     */
    public function validate(Validation $validator, $attribute): bool
    {
        $allowEmpty = $this->getOption('allowEmpty');
        $value = $validator->getValue($attribute);

        if ($allowEmpty && ((is_scalar($value) && (string) $value === '') || is_null($value))) {
            return true;
        }

        if (is_string($value) && preg_match("!^#([0-9a-f]{3,3}|[0-9a-f]{6,6})$!i", $value)) {
            return true;
        }

        $message = ($this->hasOption('message') ? $this->getOption('message') : 'Not color');

        $validator->appendMessage(new Message($message, $attribute, 'ColorValidator'));

        return false;
    }

}
