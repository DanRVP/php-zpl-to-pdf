<?php

declare(strict_types=1);

namespace PhpZpl;

use Exception;

class ZplFactory
{
    /**
     * Instantiate a new ZPL instance from a stream
     *
     * @param resource $resource
     * @return Zpl
     */
    public static function fromStream($resource): Zpl
    {
        return new Zpl(new Stream($resource));
    }

    /**
     * Instantiate a new ZPL instance from a string
     *
     * @param string $string ZPL String to parse
     * @param string $stream_location (Optional) Defaults to `php://temp`. Resulting stream resource must be seekable.
     * @return Zpl
     */
    public static function fromString(string $string, string $stream_location = 'php://temp'): Zpl
    {
        $resource = fopen($stream_location, 'rw');
        if (!$resource) {
            throw new Exception('Unable to fopen php://temp. Last error: ' . error_get_last());
        }

        if(fputs($resource, $string) === false) {
            throw new Exception('Unable to write to php://temp. Last error: ' . error_get_last());
        }

        if (!rewind($resource)) {
            throw new Exception('Unable to rewind php://temp resource. Last error: ' . error_get_last());
        }

        return static::fromStream($resource);
    }

    /**
     * Instantiate a new ZPL instance from a file
     *
     * @param string $filepath Relative or absolute path to ZPL file
     * @return Zpl
     */
    public static function fromFile(string $filepath): Zpl
    {
        $resource = fopen($filepath, 'r');
        if (!$resource) {
            throw new Exception("Unable to read file '$filepath'. Last error: " . error_get_last());
        }

        return static::fromStream($resource);
    }
}
