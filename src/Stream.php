<?php

declare(strict_types=1);

namespace PhpZpl;

use Exception;

/**
 * @see https://lisperator.net/pltut/parser/input-stream
 */
class Stream
{
    /**
     * The size in bytes of the stream
     */
    private int $size = 0;

    /**
     * Whether we have hit the current stream end
     */
    private bool $stream_end = false;

    /**
     * Construct a new Stream instance
     *
     * @param resource $resource Should be a resource stream
     */
    public function __construct(private $resource)
    {
        $stats = fstat($resource);
        if (!$stats) {
            throw new Exception('Unable to get stream length');
        }

        /**
         * Offset 7 in the stats array is size in bytes
         *
         * @see https://www.php.net/manual/en/function.fstat.php
         * @see https://www.php.net/manual/en/function.stat.php
         * */
        $this->size = $stats[7];
    }

    /**
     * Called when GC removes the object from memory
     */
    public function __destruct()
    {
        $this->close();
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
        if (!$this->stream_end) {
            $this->seek(-1);
        }

        return $char;
    }

    /**
     * Return the next byte and increase the stream position by one
     *
     * @return string
     */
    public function next(): ?string
    {
        $position = $this->getPosition();
        if (feof($this->resource) || $position + 1 > $this->size) {
            $this->stream_end = true;
            return '';
        }

        $char = fread($this->resource, 1);
        if ($char === false) {
            throw new Exception("Unable to read stream at position $position");
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
        $this->stream_end = false;
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
            throw new Exception("Unable to read current stream position. Ensure your stream is seekable");
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
        // Check hasn't alrready been closed
        if (is_resource($this->resource)) {
            fclose($this->resource);
        }
    }
}
