<?php

/*
 * Phalcon Ext
 * Copyright (c) 2016 David Hübner
 * This source file is subject to the New BSD License
 * Licence is bundled with this package in the file docs/LICENSE.txt
 * Author: David Hübner <david.hubner@gmail.com>
 */

namespace PhalconExt\Mvc\Model\Traits;

/**
 * Adds login rate limit support to target model
 * 
 * Class using this trait must define 2 constants:
 * 
 *     MAX_FAILED_LOGIN_ATTEMPTS
 *     ACCOUNT_LOCK_DURATION (in seconds)
 *
 * Usage:
 * 
 * class ModelWithLoginRateLimit extends \Phalcon\Mvc\Model
 * {
 *     use \PhalconExt\Mvc\Model\Traits\RateLimitLoginTrait;
 * 
 *     const MAX_FAILED_LOGIN_ATTEMPTS = 10;
 *     const ACCOUNT_LOCK_DURATION = 600;
 * }
 *
 * @author     David Hübner <david.hubner at google.com>
 * @version    Release: @package_version@
 * @since      Release 1.0
 */
trait RateLimitLoginTrait
{

    /**
     * @var int $_failedLoginAttempts - failed login attempts 
     */
    private $_failedLoginAttempts = 0;

    /**
     * @var int $_failedLoginTs - last failed login timestamp
     */
    private $_failedLoginTs = 0;

    /**
     * @var int $lastLoginTs - last successfull login timestamp
     */
    public $lastLoginTs;

    /**
     * @var int $lastLoginIp - last successfull login IP address
     */
    public $lastLoginIp;

    /**
     * Returns actual failed login attempts
     *
     * @author  David Hübner <david.hubner at google.com>
     * @return  int
     */
    public function getFailedLoginAttempts()
    {
        return $this->_failedLoginAttempts;
    }

    /**
     * Checks wheather account is locked to prevent brute force attacs
     *
     * @author  David Hübner <david.hubner at google.com>
     * @return  false | int - remaining lock duration
     */
    public function hasAccountLocked()
    {
        // not engough failed attempts
        if ($this->_failedLoginAttempts < self::MAX_FAILED_LOGIN_ATTEMPTS) {
            return false;
        }

        $diff = $this->_failedLoginTs + self::ACCOUNT_LOCK_DURATION - time();

        // account lock in process
        if ($diff > 0) {
            return $diff;
        }

        // releasing lock
        $this->_failedLoginAttempts = 0;
        $this->_failedLoginTs = 0;

        return false;
    }

    /**
     * Increments failed login attempts
     *
     * @author  David Hübner <david.hubner at google.com>
     * @return  bool
     */
    public function incrementFailedLoginAttempts()
    {
        $this->_failedLoginAttempts += 1;
        $this->_failedLoginTs = time();
        return $this->save();
    }

    /**
     * Resets failed login attempts
     *
     * @author  David Hübner <david.hubner at google.com>
     * @param   string $ipAddress - login IP address, default NULL
     * @return  bool
     */
    public function resetFailedLoginAttempts($ipAddress = NULL)
    {
        $this->_failedLoginAttempts = 0;
        $this->_failedLoginTs = 0;
        $this->lastLoginTs = time();
        $this->lastLoginIp = $ipAddress;
        return $this->save();
    }

}
