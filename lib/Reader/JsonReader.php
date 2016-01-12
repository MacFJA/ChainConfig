<?php

namespace MacFJA\ChainConfig\Reader;

/**
 * Class JsonReader.
 * Reader for `.json` files.
 *
 * @package MacFJA\ChainConfig
 * @author  MacFJA
 * @license MIT
 */
class JsonReader extends AbstractReader
{

    /**
     * Get the list of supported file extension
     * @return string[]
     */
    public function getExtensions()
    {
        return array('json');
    }

    /**
     * Read the file
     * @param string $filename
     * @return array
     */
    public function read($filename)
    {
        return json_decode($this->getContentWithLock($filename), true);
    }
}
