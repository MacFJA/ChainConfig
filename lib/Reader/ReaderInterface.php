<?php

namespace MacFJA\ChainConfig\Reader;

/**
 * Interface ReaderInterface.
 * The configuration file reader interface
 *
 * @package MacFJA\ChainConfig
 * @author  MacFJA
 * @license MIT
 */
interface ReaderInterface
{
    /**
     * Check if the loader can read the given file
     *
     * @param string $filename The file to check
     * @return bool `true` if the file can be read by the reader, `false` otherwise
     */
    public function canRead($filename);

    /**
     * Get the list of supported file extension
     *
     * @return string[] The list of extension (without the ".")
     */
    public function getExtensions();

    /**
     * Read the file
     *
     * @param string $filename The file to read
     * @return array The read data
     */
    public function read($filename);
}
