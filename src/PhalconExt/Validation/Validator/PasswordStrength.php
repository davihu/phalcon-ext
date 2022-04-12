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
 * Validates password strength
 *
 * <code>
 * new \PhalconExt\Validation\Validator\PasswordStrength([
 *     'minScore' => {[1-4] - minimal password score},
 *     'message' => {string - validation message},
 *     'allowEmpty' => {bool - allow empty value}
 * ])
 * </code>
 *
 * @author     David Hübner <david.hubner at google.com>
 * @version    Release: @package_version@
 * @since      Release 1.0
 */
class PasswordStrength extends AbstractValidator
{

    const MIN_VALID_SCORE = 2;

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

        $minScore = ($this->hasOption('minScore') ? $this->getOption('minScore') : self::MIN_VALID_SCORE);

        if (is_string($value) && $this->countScore($value) >= $minScore) {
            return true;
        }

        $message = ($this->hasOption('message') ? $this->getOption('message') : 'Password is too weak');

        $validation->appendMessage(new Message($message, $attribute, 'PasswordStrengthValidator'));

        return false;
    }

    /**
     * Calculates password strength score
     *
     * @author  David Hübner <david.hubner at google.com>
     * @param   string $value - password
     * @return  int (1 = very weak, 2 = weak, 3 = medium, 4+ = strong)
     */
    private function countScore($value)
    {
        $score = 0;
        $hasLower = preg_match('![a-z]!', $value);
        $hasUpper = preg_match('![A-Z]!', $value);
        $hasNumber = preg_match('![0-9]!', $value);

        if ($hasLower && $hasUpper) {
            ++$score;
        }
        if (($hasNumber && $hasLower) || ($hasNumber && $hasUpper)) {
            ++$score;
        }
        if (preg_match('![^0-9a-zA-Z]!', $value)) {
            ++$score;
        }

        $length = mb_strlen($value);

        if ($length >= 16) {
            $score += 2;
        } elseif ($length >= 8) {
            ++$score;
        } elseif ($length <= 4 && $score > 1) {
            --$score;
        } elseif ($length > 0 && $score === 0) {
            ++$score;
        }

        return $score;
    }
}
