<?php

namespace MacFJA\ChainConfig\Reader;

/**
 * Class IniReader.
 * Reader for `.ini` and `.properties` files.
 *
 * @package MacFJA\ChainConfig
 * @author  MacFJA
 * @license MIT
 */
class IniReader extends AbstractReader
{

    /**
     * Get the list of supported file extension
     *
     * @return string[] The list of extension (without the ".")
     */
    public function getExtensions()
    {
        return array('ini', 'properties');
    }

    /**
     * Read the file
     *
     * @param string $filename The file to read
     * @return array The read data
     */
    public function read($filename)
    {
        return parse_ini_string($this->getContentWithLock($filename), true);
    }
}
