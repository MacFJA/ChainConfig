<?php

namespace MacFJA\ChainConfig\Reader;

/**
 * Class XmlReader.
 * Reader for `.xml` files.
 *
 * @package MacFJA\ChainConfig
 * @author  MacFJA
 * @license MIT
 */
class XmlReader extends AbstractReader
{

    /**
     * Get the list of supported file extension
     * @return string[]
     */
    public function getExtensions()
    {
        return array('xml');
    }

    /**
     * Read the file
     * @param string $filename
     * @return array
     */
    public function read($filename)
    {
        $xml = simplexml_load_string($this->getContentWithLock($filename));
        $json = json_encode($xml);
        $array = json_decode($json, true);
        return $array;
    }
}
