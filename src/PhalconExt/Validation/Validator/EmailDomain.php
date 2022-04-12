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
 * Validates email domain existence via DNS
 *
 * <code>
 * new \PhalconExt\Validation\Validator\EmailDomain([
 *     'message' => {string - validation message},
 *     'allowEmpty' => {bool - allow empty value}
 * ])
 * </code>
 *
 * @author     David Hübner <david.hubner at google.com>
 * @version    Release: @package_version@
 * @since      Release 1.0
 */
class EmailDomain extends AbstractValidator
{

    const DOMAIN_CHECK_TYPE = 'MX';

    /**
     * Value validation
     *
     * @author  David Hübner <david.hubner at google.com>
     * @param   \Phalcon\Validation $validation - validation object
     * @param   string $attribute - validated attribute
     * @return  bool
     */
    public function validate(Validation $validation, $attribute): bool
    {
        $allowEmpty = $this->getOption('allowEmpty');
        $value = $validation->getValue($attribute);

        if ($allowEmpty && ((is_scalar($value) && (string) $value === '') || is_null($value))) {
            return true;
        }

        if (is_string($value) && strpos($value, '@') !== false) {
            list($mail, $host) = explode('@', $value);
            if (checkdnsrr($host, self::DOMAIN_CHECK_TYPE)) {
                return true;
            }
        }

        $message = ($this->hasOption('message') ? $this->getOption('message') : 'Invalid email domain');

        $validation->appendMessage(new Message($message, $attribute, 'EmailDomainValidator'));

        return false;
    }

}
