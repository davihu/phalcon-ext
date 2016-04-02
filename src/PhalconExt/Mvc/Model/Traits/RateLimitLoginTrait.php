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
 * <code>
 * class ModelWithLoginRateLimit extends \Phalcon\Mvc\Model
 * {
 *     use \PhalconExt\Mvc\Model\Traits\RateLimitLoginTrait;
 *
 *     const MAX_FAILED_LOGIN_ATTEMPTS = 10;
 *     const ACCOUNT_LOCK_DURATION = 600;
 * }
 * </code>
 *
 * @author     David Hübner <david.hubner at google.com>
 * @version    Release: @package_version@
 * @since      Release 1.0
 */
trait RateLimitLoginTrait
{

    /**
     * @var int $failedLoginAttempts - failed login attempts
     */
    protected $failedLoginAttempts = 0;

    /**
     * @var int $failedLoginTs - last failed login timestamp
     */
    protected $failedLoginTs = 0;

    /**
     * @var int $lastLoginTs - last successfull login timestamp
     */
    protected $lastLoginTs;

    /**
     * @var int $lastLoginIp - last successfull login IP address
     */
    protected $lastLoginIp;

    /**
     * Returns actual failed login attempts
     *
     * @author  David Hübner <david.hubner at google.com>
     * @return  int
     */
    public function getFailedLoginAttempts()
    {
        return $this->failedLoginAttempts;
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
        if ($this->failedLoginAttempts < self::MAX_FAILED_LOGIN_ATTEMPTS) {
            return false;
        }

        $diff = $this->failedLoginTs + self::ACCOUNT_LOCK_DURATION - time();

        // account lock in process
        if ($diff > 0) {
            return $diff;
        }

        // releasing lock
        $this->failedLoginAttempts = 0;
        $this->failedLoginTs = 0;

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
        $this->failedLoginAttempts += 1;
        $this->failedLoginTs = time();
        return $this->save();
    }

    /**
     * Resets failed login attempts
     *
     * @author  David Hübner <david.hubner at google.com>
     * @param   string $ipAddress - login IP address, default null
     * @return  bool
     */
    public function resetFailedLoginAttempts($ipAddress = null)
    {
        $this->failedLoginAttempts = 0;
        $this->failedLoginTs = 0;
        $this->lastLoginTs = time();
        $this->lastLoginIp = $ipAddress;
        return $this->save();
    }

    /**
     * Gets last login timestamp
     *
     * @author  David Hübner <david.hubner at google.com>
     * @return  int | null
     */
    public function getLastLoginTs()
    {
        return $this->lastLoginTs;
    }

    /**
     * Gets last login ip address
     *
     * @author  David Hübner <david.hubner at google.com>
     * @return  string | null
     */
    public function getLastLoginIp()
    {
        return $this->lastLoginIp;
    }
}
