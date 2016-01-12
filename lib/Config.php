<?php

namespace MacFJA\ChainConfig;

use MacFJA\ChainConfig\Collection\MultiPartKeyArray;
use MacFJA\ChainConfig\Collection\OrderedGroup;
use MacFJA\ChainConfig\Reader\ReaderInterface;
use Traversable;

/**
 * Class Config.
 * Configuration loader and reader.
 *
 * @package MacFJA\ChainConfig
 * @author  MacFJA
 * @license MIT
 */
class Config implements \IteratorAggregate, \Countable
{
    /** @var OrderedGroup */
    protected $paths;
    /** @var ReaderInterface[] Ordered list of all configuration reader */
    protected $readers = array();
    /** @var MultiPartKeyArray All current configuration */
    protected $data;
    /** @var callable[] List of key transformation callback */
    protected $keyCallbacks = array();
    /** @var callable[] List of value transformation callback */
    protected $valueCallbacks = array();

    /**
     * Config constructor.
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        $this->data = new MultiPartKeyArray($data, '.');
        $this->paths = new OrderedGroup();
    }


    /**
     * Get the configuration value for a key
     *
     * @param string $key The configuration key
     * @param null $default The default value to use if the configuration don't exist
     * @param bool|false $setIfDefault If true, the default value will be set for all future call
     * @return mixed
     */
    public function get($key, $default = null, $setIfDefault = false)
    {
        $key = $this->callKeyCallbacks($key);

        $parts = explode('.', $key);
        $fileIdentifier = reset($parts);
        if (!$this->data->hasKey($key)) {
            $this->loadFiles($fileIdentifier);
        }
        // The key still does not exist.
        if (!$this->data->hasKey($key)) {
            $value = $default;
            if ($setIfDefault === true) {
                $this->data->addValue($key, $value);
            }
        } else {
            $value = $this->data->getValue($key);
        }

        return $this->callValueCallbacks($key, $value);
    }

    /**
     * Return the actual configuration.
     * It contains all loaded configuration, configuration set by default
     *
     * @return array
     */
    public function getCurrentConfigurations()
    {
        return $this->data->toArray();
    }

    /* ------------------------------
     *  "Setters" (append/prepend)
     * ------------------------------
     *  For paths, readers,
     *  keyCallbacks, valueCallbacks
     */

    /**
     * Add a directory path at the end of paths to read.
     *
     * @param string $path The path to the directory
     * @param string $group The path group
     * @return $this
     */
    public function appendPath($path, $group = OrderedGroup::DEFAULT_GROUP)
    {
        $this->paths->appendValue($path, $group);
        return $this;
    }

    /**
     * Add a directory path at the beginning of the path list to read.
     *
     * @param string $path The path to the directory
     * @param string $group The path group
     * @return $this
     */
    public function prependPath($path, $group = OrderedGroup::DEFAULT_GROUP)
    {
        $this->paths->prependValue($path, $group);
        return $this;
    }

    /**
     * Order a group of path
     *
     * @param string $group The group
     * @param string|null $before The group where `$group` must be before
     * @param string|null $after The group where `$group` must be after
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function orderGroupPath($group, $before = null, $after = null)
    {
        $this->paths->setGroupPosition($group, $before, $after);
        return $this;
    }

    /**
     * Add a (configuration) file reader at the end of list of reader to used.
     *
     * @param ReaderInterface $reader The reader object
     * @return $this
     */
    public function appendReader($reader)
    {
        $this->readers[] = $reader;
        return $this;
    }

    /**
     * Add a (configuration) file reader at the beginning of list of reader to used.
     *
     * @param ReaderInterface $reader The reader object
     * @return $this
     */
    public function prependReader($reader)
    {
        array_unshift($this->readers, $reader);
        return $this;
    }

    /**
     * Add a callback function at the end of list of functions to used on key.
     *
     * @param callable $callable A callable function
     * @return $this
     */
    public function appendKeyCallback($callable)
    {
        $this->keyCallbacks[] = $callable;
        return $this;
    }

    /**
     * Add a callback function at the beginning of list of functions to used on key.
     *
     * @param callable $callable A callable function
     * @return $this
     */
    public function prependKeyCallback($callable)
    {
        array_unshift($this->keyCallbacks, $callable);
        return $this;
    }

    /**
     * Add a callback function at the end of list of functions to used on value.
     *
     * @param callable $callable A callable function
     * @return $this
     */
    public function appendValueCallback($callable)
    {
        $this->valueCallbacks[] = $callable;
        return $this;
    }

    /**
     * Add a callback function at the beginning of list of functions to used on value.
     *
     * @param callable $callable A callable function
     * @return $this
     */
    public function prependValueCallback($callable)
    {
        array_unshift($this->valueCallbacks, $callable);
        return $this;
    }

    /* ------------------------------
     *  Callback caller
     * ------------------------------
     *  Function to call all
     *  callback for key and values
     */
    /**
     * Execute all callback on a multi-part key
     *
     * @param string $key The multi-part key
     * @return string
     */
    protected function callKeyCallbacks($key)
    {
        foreach ($this->keyCallbacks as $callback) {
            if (is_callable($callback)) {
                $key = call_user_func($callback, $key);
            }
        }
        return $key;
    }

    /**
     * Execute all callback on the value
     *
     * @param string $key The key of the data
     * @param mixed $value The value
     * @return mixed
     */
    protected function callValueCallbacks($key, $value)
    {
        foreach ($this->valueCallbacks as $callback) {
            if (is_callable($callback)) {
                $value = call_user_func($callback, $key, $value);
            }
        }
        return $value;
    }

    /* ------------------------------
     *  Utils functions
     * ------------------------------
     *  Load a file name in config
     *  data, get the list of all
     *  extension
     */

    /**
     * Load all file with the corresponding filename (without extension)
     *
     * @param string $fileIdentifier The filename without extension
     *
     * @return void
     */
    protected function loadFiles($fileIdentifier)
    {
        $extensions = $this->getAllExtensions();
        foreach ($this->paths->getAllValues() as $path) {
            if (!file_exists($path) || !is_dir($path)) {
                // Path is simply ignored
                continue;
            }
            $files = scandir($path);

            // Handle .dist files
            $distFiles = array();
            foreach ($files as $index => &$filename) {
                if (substr($filename, -strlen('.dist')) !== '.dist') {
                    // Not a `.dist` dist file, keep it
                    continue;
                }

                $fileWithoutDist = substr($filename, 0, strlen($filename) - strlen('.dist'));
                if (in_array($fileWithoutDist, $files, true)) {
                    // A file without the `.dist` exist, don't keep the `.dist`
                    unset($files[$index]);
                    continue;
                }

                $distFiles[] = $fileWithoutDist;
                $filename = $fileWithoutDist;
            }
            unset($filename);

            // Remove file with the wrong name
            $files = array_filter($files, function ($file) use ($fileIdentifier, $extensions) {
                if (substr($file, 0, strlen($fileIdentifier) + 1) !== $fileIdentifier . '.') {
                    return false;
                }
                return in_array(substr($file, strlen($fileIdentifier) + 1), $extensions, true);
            });

            // Sort file according to the reader order
            usort($files, function ($fileA, $fileB) use ($fileIdentifier, $extensions) {
                $extensionA = substr($fileA, strlen($fileIdentifier) + 1);
                $extensionB = substr($fileB, strlen($fileIdentifier) + 1);

                return array_search($extensionA, $extensions, true) - array_search($extensionB, $extensions, true);
            });

            // Load the files
            foreach ($files as $file) {
                foreach ($this->readers as $reader) {
                    if ($reader->canRead($file)) {
                        if (in_array($file, $distFiles, true)) {
                            $file .= '.dist';
                        }
                        $this->data->addArray(
                            $reader->read($path . DIRECTORY_SEPARATOR . $file),
                            $fileIdentifier,
                            false
                        );
                    }
                }
            }
        }
    }

    /**
     * Get the list of handled extension of all readers
     *
     * @return string[]
     */
    protected function getAllExtensions()
    {
        $extensions = array();
        foreach ($this->readers as $reader) {
            $extensions = array_merge($extensions, $reader->getExtensions());
        }

        return $extensions;
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
        return $this->data->getIterator();
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
        return $this->data->count();
    }
}
