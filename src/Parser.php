<?php

declare(strict_types=1);

namespace PhpZpl;

class Parser
{
    public function __construct(private Lexer $lexer)
    {
    }

    /**
     * Parse the commands picked out by the lexer
     *
     * @return void
     */
    public function parse()
    {
        $commands = [];
        $this->lexer->resetStream();
        while ($result = $this->lexer->readNext()) {
            $commands[] = $this->parseCommand($result);
        }

        return $commands;
    }

    /**
     * Split the command into its signature and its arguments to generate our command list
     *
     * @return void
     */
    private function parseCommand(string $command_string)
    {
        // Commands are lexed with their prefix. We can remove the prefixes when building our function names
        $command = substr($command_string, 1, 2);
        $arguments = substr($command_string, 3);

        // As far as I can tell from the ZPLII spec the only single letter command is "^A".
        // Therefore we'll just address this very simply and directly.
        if (strtoupper(substr($command, 1, 1)) == 'A') {
            if ($command === 'A@') {
                // "@" is not a valid symbol in PHP for declaring functions
                $command = 'Aat';
            } else {
                $arguments = substr($command, 0, 1) . $arguments;
                $command = 'A';
            }
        }

        return [
            strtoupper($command),
            explode(',', $arguments),
        ];
    }
}
