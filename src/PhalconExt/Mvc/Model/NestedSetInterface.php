<?php
/*
 * Phalcon Ext
 * Copyright (c) 2016 David Hübner
 * This source file is subject to the New BSD License
 * Licence is bundled with this package in the file docs/LICENSE.txt
 * Author: David Hübner <david.hubner@gmail.com>
 */
namespace PhalconExt\Mvc\Model;

use Phalcon\Mvc\ModelInterface;

/**
 * Nested set (multi root tree) interface
 * @author david
 */
interface NestedSetInterface extends ModelInterface
{

    /**
     * Gets root node
     * @return  mixed
     */
    public function getRoot();

    /**
     * Gets left value
     * @return  int
     */
    public function getLeft();

    /**
     * Gets right value
     * @return  int
     */
    public function getRight();

    /**
     * Gets level
     * @return  int
     */
    public function getLevel();

    /**
     * Checks if node is root
     * @return  bool
     */
    public function isRoot();

    /**
     * Checks if node is leaf
     * @return  bool
     */
    public function isleaf();

    /**
     * Checks if node is descendant of subject
     * @param   \PhalconExt\Mvc\Model\NestedSetInterface $subject - subject node
     * @return  bool
     */
    public function isDescendantOf(NestedSetInterface $subject);

    /**
     * Checks if node is ancestor of subject
     * @param   \PhalconExt\Mvc\Model\NestedSetInterface $subject - subject node
     * @return  bool
     */
    public function isAncestorOf(NestedSetInterface $subject);

    /**
     * Finds all root nodes
     * @static
     * @param   string $extraCond - query extra conditions, default null
     * @param   array $extraBind - query extra bindings, default array()
     * @param   string $columns - selected columns
     * @return  \Phalcon\Mvc\Model\ResultsetInterface
     */
    public static function findRoots($extraCond = null, array $extraBind = array(), $columns = null);

    /**
     * Finds all descendants
     * @param   string $extraCond - query extra conditions, default null
     * @param   array $extraBind - query extra bindings, default array()
     * @param   string $columns - selected columns, default null
     * @param   int $depth - how many levels to return, default null
     * @return  \Phalcon\Mvc\Model\ResultsetInterface
     */
    public function findDescendants($extraCond = null, array $extraBind = array(), $columns = null, $depth = null);

    /**
     * Finds all children
     * @param   string $extraCond - query extra conditions, default null
     * @param   array $extraBind - query extra bindings, default array()
     * @param   string $columns - selected columns, default null
     * @return  \Phalcon\Mvc\Model\ResultsetInterface
     */
    public function findChildren($extraCond = null, array $extraBind = array(), $columns = null);

    /**
     * Finds all ancestors
     * @param   string $extraCond - query extra conditions, default null
     * @param   array $extraBind - query extra bindings, default array()
     * @param   string $columns - selected columns, default null
     * @param   int $depth - how many levels to return, default null
     * @return  \Phalcon\Mvc\Model\ResultsetInterface
     */
    public function findAncestors($extraCond = null, array $extraBind = array(), $columns = null, $depth = null);

    /**
     * Finds parent node
     * @param   string $columns - selected columns, default null
     * @return  \PhalconExt\Mvc\Model\NestedSetInterface | false
     */
    public function findParent($columns = null);

    /**
     * Finds previous sibling or previous root
     * @param   string $columns - selected columns, default null
     * @return  \PhalconExt\Mvc\Model\NestedSetInterface | false
     */
    public function findPrev($columns = null);

    /**
     * Finds next sibling or next root
     * @param   string $columns - selected columns, default null
     * @return  \PhalconExt\Mvc\Model\NestedSetInterface | false
     */
    public function findNext($columns = null);
}
