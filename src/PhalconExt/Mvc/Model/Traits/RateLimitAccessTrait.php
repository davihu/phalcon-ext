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
 * Adds access rate limit support to target model
 * Rate limit can be set as dialy or hourly
 *
 * Class using this trait must define 2 constants:
 *
 *     DEFAULT_MAX_ACCESS_RATE_LIMIT
 *     DEFAULT_ACCESS_RATE_LIMIT_PERIOD (0=hourly|1=dialy)
 *
 * <code>
 * class ModelWithAccessRateLimit extends \Phalcon\Mvc\Model
 * {
 *     use \PhalconExt\Mvc\Model\Traits\RateLimitAccessTrait;
 *
 *     const DEFAULT_MAX_ACCESS_RATE_LIMIT = 3600;
 *     const DEFAULT_ACCESS_RATE_LIMIT_PERIOD = 0;
 * }
 * </code>
 *
 * @author     David Hübner <david.hubner at google.com>
 * @version    Release: @package_version@
 * @since      Release 1.0
 */
trait RateLimitAccessTrait
{

    /**
     * @var int $accessLimitTs - last access timestamp
     */
    private $accessLimitTs = 0;

    /**
     * @var int $accessLimitUsed - access used in actual period
     */
    private $accessLimitUsed = 0;

    /**
     * @var int $accessLimitPeriod - access limit period, 0 = hourly, 1 = dialy
     */
    private $accessLimitPeriod = self::DEFAULT_ACCESS_RATE_LIMIT_PERIOD;

    /**
     * Gets remaining access limit rate
     *
     * @author  David Hübner <david.hubner at google.com>
     * @param   int $limit - max limit, default self::DEFAULT_MAX_ACCESS_RATE_LIMIT
     * @return  int
     */
    public function getRemainingAccessRateLimit($limit = self::DEFAULT_MAX_ACCESS_RATE_LIMIT)
    {
        if (empty($this->accessLimitTs)) {
            return $limit;
        }

        list($ma, $da, $ha) = explode('-', date('n-j-g', $this->accessLimitTs));
        list($mn, $dn, $hn) = explode('-', date('n-j-g'));

        if ($this->accessLimitPeriod == 1) {
            if ($ma == $mn && $da == $dn) {
                return ($limit - $this->accessLimitUsed);
            } else {
                return $limit;
            }
        } else {
            if ($ma == $mn && $da == $dn && $ha == $hn) {
                return ($limit - $this->accessLimitUsed);
            } else {
                return $limit;
            }
        }
    }

    /**
     * Incremets access limit rate
     * Automatically resets for new period
     *
     * @author  David Hübner <david.hubner at google.com>
     * @return  bool
     */
    public function incrementAccessRateLimit()
    {
        if (empty($this->accessLimitTs)) {
            $this->accessLimitUsed = 1;
        } else {
            list($ma, $da, $ha) = explode('-', date('n-j-g', $this->accessLimitTs));
            list($mn, $dn, $hn) = explode('-', date('n-j-g'));

            if ($this->accessLimitPeriod == 1) {
                if ($ma == $mn && $da == $dn) {
                    ++$this->accessLimitUsed;
                } else {
                    $this->accessLimitUsed = 1;
                }
            } else {
                if ($ma == $mn && $da == $dn && $ha == $hn) {
                    ++$this->accessLimitUsed;
                } else {
                    $this->accessLimitUsed = 1;
                }
            }
        }

        $this->accessLimitTs = time();

        return $this->save();
    }

    /**
     * Gets access limit rate period
     *
     * @author  David Hübner <david.hubner at google.com>
     * @return  string {dialy | hourly}
     */
    public function getAccessRateLimitPeriod()
    {
        return ($this->accessLimitPeriod ? 'dialy' : 'hourly');
    }

    /**
     * Sets access limit rate period
     *
     * @author  David Hübner <david.hubner at google.com>
     * @param   string $period - access limit rate period, {dialy | hourly}
     * @param   bool $save - persist to DB, default true
     * @return  bool
     * @
     */
    public function setAccessRateLimitPeriod($period, $save = true)
    {
        $per = strtolower($period);

        if (!in_array($per, array('dialy', 'hourly'))) {
            throw new \InvalidArgumentException('Unknown period ' . $period . ', expected dialy or hourly');
        }

        $this->accessLimitPeriod = ($per == 'dialy' ? 1 : 0);

        if ($save) {
            return $this->save();
        } else {
            return true;
        }
    }
}
