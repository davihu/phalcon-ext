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
 * Adds soft delete support to the model
 *
 * @author     David Hübner <david.hubner at google.com>
 * @version    Release: @package_version@
 * @since      Release 1.0
 */
trait SoftDeleteTrait
{

    /**
     * @var bool $trash - record is trashed
     */
    protected $trash = false;

    /**
     * Trashes actual item
     *
     * @author  David Hübner <david.hubner at google.com>
     * @return  bool
     */
    public function trash()
    {
        $this->trash = true;
        return self::update();
    }

    /**
     * Restores actual item
     *
     * @author  David Hübner <david.hubner at google.com>
     * @return  bool
     */
    public function restore()
    {
        $this->trash = false;
        return self::update();
    }

    /**
     * Gets the trash field
     *
     * @author  David Hübner <david.hubner at google.com>
     * @return  int
     */
    public function getTrash()
    {
        return $this->trash;
    }

    /**
     * Sets the trash field
     *
     * @author  David Hübner <david.hubner at google.com>
     * @param   int $trash
     * @return  self
     */
    public function setTrash($trash)
    {
        $this->trash = boolval($trash);
        return $this;
    }

    /**
     * Count items in recycle bin
     *
     * @static
     * @author  David Hübner <david.hubner at google.com>
     * @return  int
     */
    public static function countTrashed()
    {
        return self::count(['conditions' => 'trash = 1']);
    }
}
