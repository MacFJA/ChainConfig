<?php

namespace MacFJA\ChainConfig\Reader;

/**
 * Class PhpReader.
 * Reader for `.php` files.
 * The file must return an array.
 *
 * @package MacFJA\ChainConfig
 * @author  MacFJA
 * @license MIT
 */
class PhpReader extends AbstractReader
{

    /**
     * Get the list of supported file extension
     * @return string[]
     */
    public function getExtensions()
    {
        return array('php');
    }

    /**
     * Read the file
     * @param string $filename
     * @return array
     */
    public function read($filename)
    {
        return include $filename;
    }
}
