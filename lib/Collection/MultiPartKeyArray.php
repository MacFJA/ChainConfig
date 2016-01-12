<?php

namespace MacFJA\ChainConfig\Collection;

/**
 * Class ArrayManipulator.
 *
 * Class for manipulating array:
 *  - Reading a multi-dimension array with a key
 *  - Checking the presence of a key in a multi-dimension array
 *  - Writing in a multi-dimension array
 *  - Flatten a multi-dimension array
 *  - Un-flatten an multi-part key array
 *
 * @package MacFJA\ChainConfig\Collection
 * @author  MacFJA
 * @license MIT
 */
class MultiPartKeyArray implements \ArrayAccess, \Serializable, \IteratorAggregate, \Countable
{
    /**
     * The part separator of the multi-part key
     * @var string
     */
    protected $keySeparator = '.';
    /** @var array The data */
    protected $data = array();

    /**
     * MultiPartKeyArray constructor.
     * @param array $data The initial data
     * @param string $keySeparator The separator for the multi-part key
     */
    public function __construct(array $data = array(), $keySeparator = '.')
    {
        $this->keySeparator = $keySeparator;
        $this->data = $data;
    }


    /**
     * Get the multi-part key separator
     * @return string
     */
    public function getKeySeparator()
    {
        return $this->keySeparator;
    }

    /**
     * Set the multi-part key separator
     * @param string $keySeparator
     */
    public function setKeySeparator($keySeparator)
    {
        $this->keySeparator = $keySeparator;
    }

    /**
     * Flatten a multi-dimension array into a one-dimension array with multi-part key.
     *
     * @param array $array The multi-dimension array
     * @param string $prefix The keys prefix
     * @param string $keySeparator The multi-part key separator
     * @return array
     */
    public static function flattenArray($array, $prefix = '', $keySeparator = '.')
    {
        $result = array();
        foreach ($array as $key => $value) {
            $longKey = ('' !== $prefix ? $prefix . $keySeparator : '') . $key;
            if (is_array($value)) {
                $result = array_merge($result, self::flattenArray($value, $longKey, $keySeparator));
            } else {
                $result[$longKey] = $value;
            }
        }
        return $result;
    }

    /**
     * Return the flatten (one-dimension array with multi-part key) version of the array.
     *
     * @return array
     */
    public function flatten()
    {
        return self::flattenArray($this->data, '', $this->keySeparator);
    }

    /**
     * Un-flatten a one dimension multi-part key array into a multi-dimension array.
     *
     * @param array $array The multi-part key array
     * @param string $keySeparator The multi-part key separator
     * @return array
     */
    public static function unFlattenArray($array, $keySeparator = '.')
    {
        $result = array();
        foreach ($array as $longKey => $value) {
            $walk = &$result;
            foreach (explode($keySeparator, $longKey) as $part) {
                if (!array_key_exists($part, $walk)) {
                    $walk[$part] = array();
                }
                $walk = &$walk[$part];
            }
            $walk = $value;
        }

        return $result;
    }

    /**
     * Merge a multi-dimension array into a multi-dimension array
     *
     * @param array $newData The multi-dimension array to add
     * @param string $prefix The keys prefix
     * @param bool $override If true the new data will override existing data
     * @return void
     */
    public function addArray($newData, $prefix = '', $override = false)
    {
        $flatten = self::flattenArray($newData, $prefix, $this->keySeparator);
        foreach ($flatten as $key => $value) {
            if ($override || !$this->hasKey($key)) {
                $this->addValue($key, $value);
            }
        }
    }

    /**
     * Search for a multi-part key with in a multi-dimension array.
     * If the key is not found `null` is returned
     *
     * @param string $key The key of the data
     *
     * @return mixed|null Returns `null` if the key is not found.
     */
    public function getValue($key)
    {
        $array = $this->data;

        $parts = explode($this->keySeparator, $key);
        foreach ($parts as $part) {
            if ('' === $part) {
                return $array;
            }
            if (!is_array($array) || !array_key_exists($part, $array)) {
                return null;
            }
            $array = $array[$part];
        }
        return $array;
    }

    /**
     * Check if a multi-part key exist in an multi-dimension array
     *
     * @param string $key The key to search
     * @return bool
     */
    public function hasKey($key)
    {
        $array = $this->data;

        $parts = explode($this->keySeparator, $key);
        foreach ($parts as $part) {
            if ('' === $part) {
                return true;
            }
            if (!is_array($array) || !array_key_exists($part, $array)) {
                return false;
            }
            $array = $array[$part];
        }
        return true;
    }

    /**
     * Add a value in multi-dimension array with a multi-part key.
     *
     * @param string $key The multi-part key of the value
     * @param mixed $value The value to add
     * @return void
     */
    public function addValue($key, $value)
    {
        $array = &$this->data;
        $parts = explode('.', $key);
        $parts = array_filter($parts);// Remove empty part
        $last = array_pop($parts);

        foreach ($parts as $part) {
            if (!array_key_exists($part, $array)) {
                $array[$part] = array();
            }

            $array = &$array[$part];
        }

        $array[$last] = $value;
    }

    /**
     * Remove a value in multi-dimension array with a multi-part key.
     *
     * @param string $key The multi-part key of the value
     * @return bool
     */
    public function removeValue($key)
    {
        if (!$this->hasKey($key)) {
            return false;
        }

        $array = &$this->data;
        $parts = explode('.', $key);
        $parts = array_filter($parts);// Remove empty part
        $last = array_pop($parts);

        foreach ($parts as $part) {
            $array = &$array[$part];
        }

        unset($array[$last]);
        return true;
    }

    /**
     * Get the data as array
     *
     * @param bool $flatten If true an one-dimension array with multi-part key
     * @return array
     */
    public function toArray($flatten = false)
    {
        if ($flatten) {
            return $this->flatten();
        }
        return $this->data;
    }

    /* ----------------------------------------
     *  Implementation of interfaces:
     *   - \ArrayAccess
     *   - \Countable
     *   - \Serializable
     *   - \IteratorAggregate
     * ----------------------------------------
     */

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return $this->hasKey($offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->getValue($offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->addValue($offset, $value);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->removeValue($offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Get the number of public properties in the ArrayObject
     * When the <b>ArrayObject</b> is constructed from an array all properties are public.
     * @link http://php.net/manual/en/arrayobject.count.php
     * @return int The number of public properties in the ArrayObject.
     */
    public function count()
    {
        return count($this->flatten());
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
            array('data' => $this->data, 'separator' => $this->keySeparator)
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
        $this->data = $data['data'];
        $this->keySeparator = $data['separator'];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return \Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->flatten());
    }
}
