<?php

declare(strict_types=1);

namespace PhpZpl;

use Exception;

/**
 * Responsible for splitting the ZPL input into its respective commands which can be handled by the parser
 *
 * ZPL is a pretty simple command language and just contains a string of commands.
 * Due to this the tokens we need to generate to feed to the parser are just each of the ZPL commands instead of having to
 * handle control structures and declarations like we would in lexer for a more complex language.
 *
 * @see https://lisperator.net/pltut/parser/token-stream
 */
class Lexer
{
    /**
     * Construct a new instance of the Lexer
     *
     * @param Stream $stream
     */
    public function __construct(private Stream $stream)
    {
    }

    /**
     * Read all ZPL commands from the input stream
     *
     * @return string[]
     */
    public function readAll(): array
    {
        $this->resetStream();

        $results = [];
        while ($result = $this->readNext()) {
            $results[] = $result;
        }

        return $results;
    }

    /**
     * Read the next command from the stream
     *
     * @return string
     */
    public function readNext(): string
    {
        $char = $this->stream->peek();
        if (empty($char)) {
            return '';
        }

        if ($char !== '^' && $char !== '~') {
            throw new Exception('Unable to parse at stream position ' . $this->stream->getPosition() + 1);
        }

        return $this->stream->next() . $this->readCommand();
    }

    /**
     * Reads the next full command from the input stream
     *
     * @return string
     */
    private function readCommand(): string
    {
        $command = '';
        while (($next = $this->stream->peek()) !== '') {
            // ^ or ~ denotes new command
            if ($next === '^' || $next === '~') {
                break;
            }

            if (preg_match('/\s/', $next) === 1 && substr($command, 0, 2) !== 'FD') {
                // Ignore whitespace when compiling our command strings if not in a field definition
                $this->stream->seek(1);
                continue;
            }

            $command .= $this->stream->next();
        }

        return $command;
    }

    /**
     * Reset the stream to the start of the stream
     *
     * @return void
     */
    public function resetStream(): void
    {
        $this->stream->reset();
    }
}
