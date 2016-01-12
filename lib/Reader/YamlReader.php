<?php

namespace MacFJA\ChainConfig\Reader;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class YamlReader.
 * Reader for `.yaml` and `.yml` files.
 * You need to have `symfony/yaml` component in your project.
 *
 * @package MacFJA\ChainConfig
 * @author  MacFJA
 * @license MIT
 */
class YamlReader extends AbstractReader
{

    /**
     * Get the list of supported file extension
     * @return string[]
     */
    public function getExtensions()
    {
        return array('yaml', 'yml');
    }

    /**
     * Read the file
     * @param string $filename
     * @return array
     *
     * @throws ParseException
     */
    public function read($filename)
    {
        return Yaml::parse($this->getContentWithLock($filename));
    }
}
