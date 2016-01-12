<?php

namespace MacFJA\ChainConfig\Collection;

use Traversable;

/**
 * Class OrderedGroup.
 * A class to get grouped and sorted values.
 *
 * @package MacFJA\ChainConfig\Collection
 * @author  MacFJA
 * @license MIT
 */
class OrderedGroup implements \IteratorAggregate, \Countable, \Serializable
{
    /** The default value group */
    const DEFAULT_GROUP = '__main__';

    /** @var array List of all values */
    protected $values = array();
    /** @var array Data about group ordering */
    protected $groups = array();


    /**
     * Add a value at the end.
     *
     * @param string|array $value The value to the directory
     * @param string $group The name of the value group
     * @return $this
     */
    public function appendValue($value, $group = self::DEFAULT_GROUP)
    {
        if (is_array($value)) {
            foreach ($value as $item) {
                $this->appendValue($item, $group);
            }
            return $this;
        }

        $this->values[] = array('value' => $value, 'group' => $group);
        return $this;
    }

    /**
     * Add a value at the beginning.
     *
     * @param string|array $value The value to the directory
     * @param string $group The name of the value group
     * @return $this
     */
    public function prependValue($value, $group = self::DEFAULT_GROUP)
    {
        if (is_array($value)) {
            rsort($value);
            foreach ($value as $item) {
                $this->prependValue($item, $group);
            }
            return $this;
        }

        array_unshift($this->values, array('value' => $value, 'group' => $group));
        return $this;
    }

    /**
     * Define the group relative position
     *
     * @param string $groupName The group
     * @param string|null $before The group where $groupName must be before
     * @param string|null $after The group where $groupName must be after
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function setGroupPosition($groupName, $before = null, $after = null)
    {
        if (null === $before && null === $after) {
            throw new \InvalidArgumentException('[OrderedGroup] You must define at least $before or $after.');
        }

        if ($before === $after) {
            throw new \InvalidArgumentException('[OrderedGroup] $before and $after must be different.');
        }

        if (!array_key_exists($groupName, $this->groups)) {
            $this->groups[$groupName] = array();
        }

        $this->groups[$groupName]['after'] = $after;
        $this->groups[$groupName]['before'] = $before;

        return $this;
    }

    /**
     * Get all values of a group
     *
     * @param string $group The group to retrieve
     * @return array
     */
    public function getGroupValues($group = self::DEFAULT_GROUP)
    {
        $values = array();
        foreach ($this->values as $value) {
            if ($value['group'] === $group) {
                $values[] = $value['value'];
            }
        }

        return $values;
    }

    /**
     * Get the unsorted list of group
     *
     * @return array
     */
    public function getAllGroups()
    {
        $groups = array();
        foreach ($this->values as $value) {
            $groups[] = $value['group'];
        }
        return array_unique($groups);
    }

    /**
     * Get the sorted list of group
     *
     * @return array
     */
    public function getSortedGroups()
    {
        $groups = $this->getAllGroups();

        foreach ($groups as $group) {
            if (!array_key_exists($group, $this->groups)) {
                continue;
            }

            $beforeGroup = $this->groups[$group]['before'];
            if (null !== $beforeGroup) {
                unset($groups[array_search($group, $groups, true)]);
                /*
                 * Array splice use an offset (numeric position), but array_search return the key
                 * Because of the unset, if $group is before $beforeGroup, the offset is changed
                 */
                $offset = array_search(array_search($beforeGroup, $groups, true), array_keys($groups), true);
                array_splice($groups, $offset, 0, array($group));
            }

            $afterGroup = $this->groups[$group]['after'];
            if (null !== $afterGroup) {
                unset($groups[array_search($group, $groups, true)]);
                /*
                 * Array splice use an offset (numeric position), but array_search return the key
                 * Because of the unset, if $group is before $beforeGroup, the offset is changed
                 */
                $offset = array_search(array_search($afterGroup, $groups, true), array_keys($groups), true);
                array_splice($groups, $offset + 1, 0, array($group));
            }
        }
        $groups = array_values($groups);

        return $groups;
    }

    /**
     * Get all values, sorted
     *
     * @return array
     */
    public function getAllValues()
    {
        $groupPaths = array();
        foreach ($this->getSortedGroups() as $group) {
            $groupPaths[$group] = $this->getGroupValues($group);
        }

        $values = array();
        foreach ($groupPaths as $group) {
            foreach ($group as $value) {
                $values[] = $value;
            }
        }

        return $values;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->getAllValues());
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->values);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize(
            array('values' => $this->values, 'groups' => $this->groups)
        );
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);

        $this->values = $data['values'];
        $this->groups = $data['groups'];
    }
}
