<?php

declare(strict_types=1);

namespace PhpZpl;

use RuntimeException;

/**
 * @see https://lisperator.net/pltut/parser/input-stream
 */
class Stream
{
    /**
     * Construct a new Stream instance
     *
     * @param resource $resource Should be a resource stream
     */
    public function __construct(private $resource)
    {
    }

    /**
     * Return the next n bytes without increasing the postion in stream
     *
     * @param int bytes How many bytes to peek ahead by
     * @return string
     */
    public function peek(): string
    {
        $char = $this->next();
        $this->seek(-1);
        return $char;
    }

    /**
     * Return the next byte and increase the stream position by one
     *
     * @return string
     */
    public function next(): ?string
    {
        if (feof($this->resource)) {
            return '';
        }

        $char = fread($this->resource, 1);
        if ($char === false) {
            throw new RuntimeException("Unable to read stream at position " . $this->getPosition());
        }

        return $char;
    }

    /**
     * Reset the position back to the start of the stream
     *
     * @return void
     */
    public function reset(): void
    {
        rewind($this->resource);
    }

    /**
     * Get the current postion of the stream
     *
     * @return integer
     */
    public function getPosition(): int
    {
        $pos = ftell($this->resource);
        if ($pos === false) {
            throw new RuntimeException("Unable to read current stream position. Ensure your stream is seekable");
        }

        return $pos;
    }

    /**
     * Move the current position of the stream
     *
     * @param integer $move_by Unsigned integer
     * @return void
     */
    public function seek(int $move_by): void
    {
        fseek($this->resource, $move_by, SEEK_CUR);
    }

    /**
     * Close the stream resource
     *
     * @return void
     */
    public function close(): void
    {
        fclose($this->resource);
    }
}
