<?php
/*
 * Phalcon Ext
 * Copyright (c) 2016 David Hübner
 * This source file is subject to the New BSD License
 * Licence is bundled with this package in the file docs/LICENSE.txt
 * Author: David Hübner <david.hubner@gmail.com>
 */
namespace PhalconExt\Mvc\Model\Traits;

use PhalconExt\Mvc\Model\NestedSetInterface;

/**
 * Adds nested set (multi root tree) support to target model
 *
 * <code>
 * class ModelWithNestedSet extends \Phalcon\Mvc\Model implements PhalconExt\Mvc\Model\NestedSetInterface
 * {
 *     use \PhalconExt\Mvc\Model\Traits\NestedSetTrait;
 * }
 * </code>
 *
 * @author     David Hübner <david.hubner at google.com>
 * @version    Release: @package_version@
 * @since      Release 1.0
 */
trait NestedSetTrait
{

    public $parentId;
    public $sequence = 0;
    protected $root;
    protected $left;
    protected $right;
    protected $level;

    /**
     * Checks if model is nested set
     *
     * @author  David Hübner <david.hubner at google.com>
     * @param   string $behavior
     * @return  bool
     */
    public static function isNestedSet()
    {
        return true;
    }

    /**
     * Gets root node
     *
     * @author  David Hübner <david.hubner at google.com>
     * @return  mixed
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * Gets left value
     *
     * @author  David Hübner <david.hubner at google.com>
     * @return  int
     */
    public function getLeft()
    {
        return $this->left;
    }

    /**
     * Gets right value
     *
     * @author  David Hübner <david.hubner at google.com>
     * @return  int
     */
    public function getRight()
    {
        return $this->right;
    }

    /**
     * Gets level
     *
     * @author  David Hübner <david.hubner at google.com>
     * @return  int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Checks if node is root
     *
     * @author  David Hübner <david.hubner at google.com>
     * @return  bool
     */
    public function isRoot()
    {
        return ($this->left == 1 ? true : false);
    }

    /**
     * Checks if node is leaf
     *
     * @author  David Hübner <david.hubner at google.com>
     * @return  bool
     */
    public function isleaf()
    {
        return ($this->right - $this->left == 1 ? true : false);
    }

    /**
     * Checks if node is descendant of subject
     *
     * @author  David Hübner <david.hubner at google.com>
     * @param   \PhalconExt\Mvc\Model\NestedSetInterface $subject - subject node
     * @return  bool
     */
    public function isDescendantOf(NestedSetInterface $subject)
    {
        if ($this->root != $subject->getRoot()) {
            return false;
        }
        if ($this->left > $subject->getLeft() && $this->right < $subject->getRight()) {
            return true;
        }
        return false;
    }

    /**
     * Checks if node is ancestor of subject
     *
     * @author  David Hübner <david.hubner at google.com>
     * @param   \PhalconExt\Mvc\Model\NestedSetInterface $subject - subject node
     * @return  bool
     */
    public function isAncestorOf(NestedSetInterface $subject)
    {
        if ($this->root != $subject->getRoot()) {
            return false;
        }
        if ($this->left < $subject->getLeft() && $this->right > $subject->getRight()) {
            return true;
        }
        return false;
    }

    /**
     * Finds all root nodes
     *
     * @static
     * @author  David Hübner <david.hubner at google.com>
     * @param   string $extraCond - query extra conditions, default null
     * @param   array $extraBind - query extra bindings, default array()
     * @param   string $columns - selected columns
     * @return  \Phalcon\Mvc\Model\ResultsetInterface
     */
    public static function findRoots($extraCond = null, array $extraBind = array(), $columns = null)
    {
        $params = array();
        $params['conditions'] = 'left = 1' . ($extraCond ? ' AND (' . $extraCond . ')' : '');
        $params['order'] = 'sequence';
        if ($extraBind) {
            $params['bind'] = $extraBind;
        }
        if ($columns) {
            $params['columns'] = $columns;
        }
        return self::find($params);
    }

    /**
     * Finds all descendants
     *
     * @author  David Hübner <david.hubner at google.com>
     * @param   string $extraCond - query extra conditions, default null
     * @param   array $extraBind - query extra bindings, default array()
     * @param   string $columns - selected columns, default null
     * @param   int $depth - how many levels to return, default null
     * @return  \Phalcon\Mvc\Model\ResultsetInterface
     */
    public function findDescendants($extraCond = null, array $extraBind = array(), $columns = null, $depth = null)
    {
        $cond = 'left > :left: AND right < :right: AND root = :root:';
        $bind = array(
            'left' => $this->left,
            'right' => $this->right,
            'root' => $this->root
        );

        if ($depth) {
            $cond .= ' AND level <= :level:';
            $bind['level'] = $this->level + $depth;
        }

        $params = array();
        $params['conditions'] = $cond . ($extraCond ? ' AND (' . $extraCond . ')' : '');
        $params['bind'] = array_merge($bind, $extraBind);
        $params['order'] = 'left';
        if ($columns) {
            $params['columns'] = $columns;
        }

        return self::find($params);
    }

    /**
     * Finds all children
     *
     * @author  David Hübner <david.hubner at google.com>
     * @param   string $extraCond - query extra conditions, default null
     * @param   array $extraBind - query extra bindings, default array()
     * @param   string $columns - selected columns, default null
     * @return  \Phalcon\Mvc\Model\ResultsetInterface
     */
    public function findChildren($extraCond = null, array $extraBind = array(), $columns = null)
    {
        return $this->findDescendants($extraCond, $extraBind, $columns, 1);
    }

    /**
     * Finds all ancestors
     *
     * @author  David Hübner <david.hubner at google.com>
     * @param   string $extraCond - query extra conditions, default null
     * @param   array $extraBind - query extra bindings, default array()
     * @param   string $columns - selected columns, default null
     * @param   int $depth - how many levels to return, default null
     * @return  \Phalcon\Mvc\Model\ResultsetInterface
     */
    public function findAncestors($extraCond = null, array $extraBind = array(), $columns = null, $depth = null)
    {
        $cond = 'root = :root: AND left < :left: AND right > :right:';
        $bind = array(
            'root' => $this->root,
            'left' => $this->left,
            'right' => $this->right
        );

        if ($depth) {
            $cond .= ' AND level >= :level:';
            $bind['level'] = $this->level - $depth;
        }

        $params = array();
        $params['conditions'] = $cond . ($extraCond ? ' AND (' . $extraCond . ')' : '');
        $params['bind'] = array_merge($bind, $extraBind);
        $params['order'] = 'left';
        if ($columns) {
            $params['columns'] = $columns;
        }

        return self::find($params);
    }

    /**
     * Finds parent node
     *
     * @author  David Hübner <david.hubner at google.com>
     * @param   string $columns - selected columns, default null
     * @return  \PhalconExt\Mvc\Model\NestedSetInterface | false
     */
    public function findParent($columns = null)
    {
        if ($this->isRoot()) {
            return null;
        }

        $params = array();
        $params['conditions'] = 'id = :parentId:';
        $params['bind'] = array(
            'parentId' => $this->parentId
        );
        if ($columns) {
            $params['columns'] = $columns;
        }

        return self::findFirst($params);
    }

    /**
     * Finds previous sibling or previous root
     *
     * @author  David Hübner <david.hubner at google.com>
     * @param   string $columns - selected columns, default null
     * @return  \PhalconExt\Mvc\Model\NestedSetInterface | false
     */
    public function findPrev($columns = null)
    {
        $params = array();

        if ($this->isRoot()) {
            $params['conditions'] = 'left = 1 AND sequence = :sequence:';
            $params['bind'] = array(
                'sequence' => $this->sequence - 1
            );
        } else {
            $params['conditions'] = 'root = :root: AND right = :right:';
            $params['bind'] = array(
                'root' => $this->root,
                'right' => $this->left - 1
            );
        }

        if ($columns) {
            $params['columns'] = $columns;
        }

        return self::findFirst($params);
    }

    /**
     * Finds next sibling or next root
     *
     * @author  David Hübner <david.hubner at google.com>
     * @param   string $columns - selected columns, default null
     * @return  \PhalconExt\Mvc\Model\NestedSetInterface | false
     */
    public function findNext($columns = null)
    {
        $params = array();

        if ($this->isRoot()) {
            $params['conditions'] = 'left = 1 AND sequence = :sequence:';
            $params['bind'] = array(
                'sequence' => $this->sequence + 1
            );
        } else {
            $params['conditions'] = 'root = :root: AND left = :left:';
            $params['bind'] = array(
                'root' => $this->root,
                'left' => $this->right + 1
            );
        }

        if ($columns) {
            $params['columns'] = $columns;
        }

        return self::findFirst($params);
    }

    /**
     * Creates new node (overrides default create method)
     *
     * @author  David Hübner <david.hubner at google.com>
     * @param   array $data - default null
     * @param   array $whiteList - default null
     * @return  boolean
     */
    public function create($data = null, $whiteList = null)
    {
        $db = $this->getDI()->get('db');

        // new node new root
        if (empty($this->parentId)) {
            $db->begin();

            $this->left = 1;
            $this->right = 2;
            $this->level = 1;
            $this->sequence = $this->getMaxSequence();

            if (parent::create($data, $whiteList)) {
                $this->root = $this->id;
                if (parent::update()) {
                    $db->commit();
                    return true;
                }
            }

            $db->rollback();
            return false;
        }

        $db->begin();

        // new node is child, we will insert node last
        $parent = $this->findTargetById($this->parentId);

        if (empty($parent)) {
            $db->rollback();
            return false;
        }

        // locking target tree
        $query = sprintf('SELECT root, left, right, level FROM %s WHERE root=\'%s\' FOR UPDATE', $this->getSource(), $parent->root);
        $this->getWriteConnection()->fetchAll($query);

        if (!$this->shiftNodes($parent->root, $parent->right, 2)) {
            $db->rollback();
            return false;
        }

        $this->root = $parent->root;
        $this->left = $parent->right;
        $this->right = $parent->right + 1;
        $this->level = $parent->level + 1;
        $this->sequence = 0;

        if (parent::create($data, $whiteList)) {
            $db->commit();
            return true;
        }

        $db->rollback();
        return false;
    }

    /**
     * Updates existing node (overrides default update method)
     *
     * @author  David Hübner <david.hubner at google.com>
     * @param   array $data - default null
     * @param   array $whiteList - default null
     * @param   \PhalconExt\Mvc\Model\NestedSetInterface $target - target node, default null
     * @param   string $mode - before|after|first|last, default last
     * @return  boolean
     */
    public function update($data = null, $whiteList = null, $target = null, $mode = 'last')
    {
        if ($target) {
            if ($mode == 'before' || $mode == 'after') {
                $this->parentId = $target->parentId;
            } elseif ($mode == 'first' || $mode == 'last') {
                $this->parentId = $target->id;
            }
        }

        $db = $this->getDI()->get('db');
        $db->begin();

        $table = $this->getSource();
        $conn = $this->getWriteConnection();

        // locking source tree
        $query = sprintf('SELECT root, left, right, level, sequence FROM %s WHERE root=\'%s\' FOR UPDATE', $table, $this->root);
        $conn->fetchAll($query);

        // parent change
        if ($this->hasChanged('parentId')) {
            // moving as root
            if (empty($this->parentId)) {
                // locking target tree
                if ($target && $this->root != $target->root) {
                    $query = sprintf('SELECT root, left, right, level, sequence FROM %s WHERE root=\'%s\' FOR UPDATE', $table, $target->root);
                    $conn->fetchAll($query);
                }

                // locking roots
                $query = sprintf('SELECT root, left, right, level, sequence FROM %s WHERE left=1 FOR UPDATE', $table);
                $conn->fetchAll($query);

                // moving to root
                if (!$this->moveAsRoot($target, $mode)) {
                    $db->rollback();
                    return false;
                }
            }
            // moving as node
            else {
                // finding target
                if (empty($target)) {
                    $target = $this->findTargetById($this->parentId);
                }
                if (empty($target)) {
                    $db->rollback();
                    return false;
                }

                // locking target tree
                if ($target && $this->root != $target->root) {
                    $query = sprintf('SELECT root, left, right, level, sequence FROM %s WHERE root=\'%s\' FOR UPDATE', $table, $target->root);
                    $conn->fetchAll($query);
                }

                // locking roots
                $query = sprintf('SELECT root, left, right, level, sequence FROM %s WHERE left=1 FOR UPDATE', $table);
                $conn->fetchAll($query);

                // moving
                if ($this->sequence && !$this->moveRoots(0)) {
                    $db->rollback();
                    return false;
                }
                if (!$this->moveNode($target, $mode)) {
                    $db->rollback();
                    return false;
                }
            }
        }
        // moving
        elseif ($target) {
            // locking target tree
            if ($target && $this->root != $target->root) {
                $query = sprintf('SELECT root, left, right, level, sequence FROM %s WHERE root=\'%s\' FOR UPDATE', $table, $target->root);
                $conn->fetchAll($query);
            }

            // moving root
            if ($this->left == 1) {
                // locking roots
                $query = sprintf('SELECT root, left, right, level, sequence FROM %s WHERE left=1 FOR UPDATE', $table);
                $conn->fetchAll($query);

                // moving
                if ($mode == 'after' && $this->sequence > $target->sequence + 1) {
                    if (!$this->moveRoots($target->sequence + 1)) {
                        $db->rollback();
                        return false;
                    }
                } elseif ($mode == 'after' && $this->sequence < $target->sequence) {
                    if (!$this->moveRoots($target->sequence)) {
                        $db->rollback();
                        return false;
                    }
                } elseif ($mode == 'before' && $this->sequence > $target->sequence) {
                    if (!$this->moveRoots($target->sequence)) {
                        $db->rollback();
                        return false;
                    }
                }
            }
            // moving node
            else {
                if (!$this->moveNode($target, $mode)) {
                    $db->rollback();
                    return false;
                }
            }
        }

        if (parent::update($data, $whiteList)) {
            $db->commit();
            return true;
        }

        $db->rollback();
        return false;
    }

    /**
     * Deletes existing node with all subtree (overrides default delete method)
     *
     * @author  David Hübner <david.hubner at google.com>
     * @param   bool $skipNested - skip tree delete
     * @return  boolean
     */
    public function delete($skipNested = false)
    {
        $db = $this->getDI()->get('db');
        $db->begin();

        // deleting all descendants
        if (empty($skipNested) && ($this->left + 1) < $this->right) {
            $descendants = $this->findDescendants();
            foreach ($descendants as $descendant) {
                if (!$descendant->deleteNode()) {
                    $db->rollback();
                    return false;
                }
            }
        }

        // shifting roots
        if (empty($this->parentId)) {
            if (!$this->shiftRoots($this->sequence, -1)) {
                $db->rollback();
                return false;
            }
        }
        // shifting nodes
        else {
            if (!$this->shiftNodes($this->root, $this->right, ($this->right - $this->left + 1) * -1)) {
                $db->rollback();
                return false;
            }
        }

        // deleting node
        if (!$this->deleteNode()) {
            $db->rollback();
            return false;
        }

        $db->commit();
        return true;
    }

    /**
     * Deletes actual node
     *
     * @author  David Hübner <david.hubner at google.com>
     * @return  boolean
     */
    public function deleteNode()
    {
        return parent::delete();
    }

    // moves node as root
    protected function moveAsRoot($target, $mode)
    {
        $size = $this->right - $this->left + 1;
        $posDiff = 1 - $this->left;
        $lvlDiff = 1 - $this->level;

        $table = $this->getSource();
        $conn = $this->getWriteConnection();

        // moving nodes
        $query = sprintf(
            'UPDATE %s SET root=\'%s\', left=left+%d, right=right+%d, level=level+%d WHERE root=\'%s\' AND left>=%d AND right<=%d', $table, $this->id, $posDiff, $posDiff, $lvlDiff, $this->root, $this->left, $this->right
        );
        if (!$conn->execute($query)) {
            return false;
        }
        // updating source tree
        if (!$this->shiftNodes($this->root, $this->right, ($size * -1))) {
            return false;
        }
        // shifting roots
        if ($target) {
            if ($mode == 'before') {
                $pos = (int) $target->sequence;
            } elseif ($mode == 'after') {
                $pos = $target->sequence + 1;
            }
        }
        if (empty($pos)) {
            $pos = $this->getMaxSequence();
        } else {
            if (!$this->shiftRoots($pos, 1)) {
                return false;
            }
        }
        // everything ok
        $this->root = $this->id;
        $this->left += $posDiff;
        $this->right += $posDiff;
        $this->level += $lvlDiff;
        $this->sequence = $pos;
        return true;
    }

    // moves node to tree
    protected function moveNode($target, $mode)
    {
        $parentDiff = 0;
        if ($mode == 'before') {
            $pos = (int) $target->left;
        } elseif ($mode == 'after') {
            $pos = $target->right + 1;
        } elseif ($mode == 'first') {
            $pos = $target->left + 1;
            $parentDiff = 1;
        } else {
            $pos = (int) $target->right;
            $parentDiff = 1;
        }
        if ($this->root == $target->root) {
            return $this->moveNodeSameTree($target, $pos, $parentDiff);
        } else {
            return $this->moveNodeAnotherTree($target, $pos, $parentDiff);
        }
    }

    // moves node in the same tree
    protected function moveNodeSameTree($target, $pos, $parentDiff)
    {
        $con = $this->getWriteConnection();
        $table = $this->getSource();
        $size = $this->right - $this->left + 1;

        // temporary moving nodes to root
        $query = sprintf(
            'UPDATE %s SET root=null WHERE root=\'%s\' AND left>=%d AND right<=%d', $table, $this->root, $this->left, $this->right
        );
        if (!$con->execute($query)) {
            return false;
        }

        // moving from left to right
        if ($this->right < $pos) {
            $query = sprintf(
                'UPDATE %s SET left=left-%d WHERE root=\'%s\' AND left>%d AND left<%d', $table, $size, $this->root, $this->right, $pos
            );
            if (!$con->execute($query)) {
                return false;
            }
            $query = sprintf(
                'UPDATE %s SET right=right-%d WHERE root=\'%s\' AND right>%d AND right<%d', $table, $size, $this->root, $this->right, $pos
            );
            if (!$con->execute($query)) {
                return false;
            }
            $posDiff = $pos - $this->right - 1;
        }
        // moving from right to left
        elseif ($this->left > $pos) {
            // moving
            $query = sprintf(
                'UPDATE %s SET left=left+%d WHERE root=\'%s\' AND left>=%d AND left<%d', $table, $size, $this->root, $pos, $this->left
            );
            if (!$con->execute($query)) {
                return false;
            }
            $query = sprintf(
                'UPDATE %s SET right=right+%d WHERE root=\'%s\' AND right>=%d AND right<%d', $table, $size, $this->root, $pos, $this->left
            );
            if (!$con->execute($query)) {
                return false;
            }
            $posDiff = $pos - $this->left;
        }

        $lvlDiff = $target->level - $this->level + $parentDiff;

        // moving nodes from root
        $query = sprintf(
            'UPDATE %s SET root=\'%s\', left=left+%d, right=right+%d, level=level+%d WHERE root IS NULL AND left>=%d AND right<=%d', $table, $target->root, $posDiff, $posDiff, $lvlDiff, $this->left, $this->right
        );
        if (!$con->execute($query)) {
            return false;
        }

        // everything ok
        $this->left += $posDiff;
        $this->right += $posDiff;
        $this->level += $lvlDiff;
        return true;
    }

    // moves node to another tree
    protected function moveNodeAnotherTree($target, $pos, $parentDiff)
    {
        $size = $this->right - $this->left + 1;
        $posDiff = $pos - $this->left;
        $lvlDiff = $target->level - $this->level + $parentDiff;

        // preparing target tree
        if (!$this->shiftNodes($target->root, $pos, $size)) {
            return false;
        }

        $table = $this->getSource();
        $conn = $this->getWriteConnection();

        // moving nodes
        $query = sprintf(
            'UPDATE %s SET root=\'%s\', left=left+%d, right=right+%d, level=level+%d WHERE root=\'%s\' AND left>=%d AND right<=%d', $table, $target->root, $posDiff, $posDiff, $lvlDiff, $this->root, $this->left, $this->right
        );
        if (!$conn->execute($query)) {
            return false;
        }

        // updating source tree
        if ($this->left > 1) {
            if (!$this->shiftNodes($this->root, $this->right, ($size * -1))) {
                return false;
            }
        }

        // everything ok
        $this->root = $target->root;
        $this->left += $posDiff;
        $this->right += $posDiff;
        $this->level += $lvlDiff;
        return true;
    }

    // finds target node by his id
    protected function findTargetById($id)
    {
        return self::findFirst(array(
                'columns' => 'id,root,left,right,level',
                'conditions' => 'id = :id:',
                'bind' => array('id' => $id),
                'for_update' => true
        ));
    }

    // Move root nodes
    protected function moveRoots($newSequence)
    {
        $table = $this->getSource();
        $conn = $this->getWriteConnection();

        // moving root as descendant
        if ($newSequence == 0) {
            $query = sprintf(
                'UPDATE %s SET sequence=sequence-1 WHERE left=1 AND sequence>%d', $table, $this->sequence
            );
        }
        // moving root after another root
        elseif ($newSequence > $this->sequence) {
            $query = sprintf(
                'UPDATE %s SET sequence=sequence-1 WHERE left=1 AND sequence>%d AND sequence<=%d', $table, $this->sequence, $newSequence
            );
        }
        // moving root before another root
        elseif ($newSequence < $this->sequence) {
            $query = sprintf(
                'UPDATE %s SET sequence=sequence+1 WHERE left=1 AND sequence>=%d AND sequence<%d', $table, $newSequence, $this->sequence
            );
        }

        if ($conn->execute($query)) {
            $this->sequence = $newSequence;
            return true;
        }

        return false;
    }

    // get actual maximum sequence
    protected function getMaxSequence()
    {
        $cnt = self::maximum(array(
                'column' => 'sequence',
                'conditions' => 'left = 1',
                'for_update' => true
        ));
        return ($cnt + 1);
    }

    // shift nodes
    protected function shiftNodes($root, $start, $delta)
    {
        $table = $this->getSource();
        $conn = $this->getWriteConnection();

        // shifting
        $query = sprintf(
            'UPDATE %s SET left=left+%d WHERE root=\'%s\' AND left>=%d', $table, $delta, $root, $start
        );
        if ($conn->execute($query)) {
            $query = sprintf(
                'UPDATE %s SET right=right+%d WHERE root=\'%s\' AND right>=%d', $table, $delta, $root, $start
            );
            if ($conn->execute($query)) {
                return true;
            }
        }

        return false;
    }

    // shift roots
    protected function shiftRoots($start, $delta)
    {
        $table = $this->getSource();
        $conn = $this->getWriteConnection();

        // shifting
        $query = sprintf(
            'UPDATE %s SET sequence=sequence+%d WHERE left=1 AND sequence>=%d', $table, $delta, $start
        );
        return $conn->execute($query);
    }
}
