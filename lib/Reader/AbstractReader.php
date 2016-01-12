<?php

namespace MacFJA\ChainConfig\Reader;

/**
 * Class AbstractReader.
 * Base class of a file reader. Implement `canRead` function and add a function for reading a file with a file lock.
 *
 * @package MacFJA\ChainConfig
 * @author  MacFJA
 * @license MIT
 */
abstract class AbstractReader implements ReaderInterface
{
    /**
     * Check if the loader can read the given file
     *
     * @param string $filename
     * @return bool
     */
    public function canRead($filename)
    {
        $canRead = false;
        $extensions = $this->getExtensions();
        foreach ($extensions as $extension) {
            $canRead |= (substr($filename, -strlen($extension) - 1) === '.' . $extension);
        }

        return $canRead;
    }

    /**
     * Read a file with file lock (prevent reading while writing the file)
     *
     * @param string $filename The path to the file to read
     * @return string The file content
     */
    protected function getContentWithLock($filename)
    {
        $handle = fopen($filename, 'rb');
        flock($handle, LOCK_SH);
        $data = stream_get_contents($handle);
        flock($handle, LOCK_UN);
        fclose($handle);
        return $data;
    }
}
