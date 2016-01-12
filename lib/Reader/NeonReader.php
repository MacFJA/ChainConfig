<?php

namespace MacFJA\ChainConfig\Reader;

use Nette\Neon\Neon;

/**
 * Class NeonReader.
 * Reader for `.neon` files.
 * You need to have `nette/neon` component in your project.
 *
 * @package MacFJA\ChainConfig
 * @author  MacFJA
 * @license MIT
 */
class NeonReader extends AbstractReader
{

    /**
     * Get the list of supported file extension
     * @return string[]
     */
    public function getExtensions()
    {
        return array('neon');
    }

    /**
     * Read the file
     * @param string $filename
     * @return array
     */
    public function read($filename)
    {
        return Neon::decode($this->getContentWithLock($filename));
    }
}
