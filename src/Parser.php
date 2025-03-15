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
     * @return array
     */
    public function parse(): array
    {
        try {
            $commands = [];
            $this->lexer->resetStream();
            while ($result = $this->lexer->readNext()) {
                $commands[] = $this->parseCommand($result);
            }

            return $commands;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * Split the command into its signature and its arguments to generate our command list
     *
     * @return array
     */
    private function parseCommand(string $command_string): array
    {
        // Commands are lexed with their prefix. We can remove the prefixes when building our function names
        $command = strtoupper(substr($command_string, 1, 2));
        $arguments = substr($command_string, 3);

        // As far as I can tell from the ZPLII spec the only single letter command is "^A".
        // Therefore we'll just address this very simply and directly.
        if (substr($command, 0, 1) == 'A') {
            if ($command === 'A@') {
                // "@" is not a valid symbol in PHP for declaring functions
                $command = 'AAT';
            } else {
                $arguments = substr($command, 0, 1) . $arguments;
                $command = 'A';
            }
        }

        $args = explode(',', $arguments);
        if (count($args) === 1 && empty($args[0])) {
            $args = [];
        }

        return [$command, $args];
    }
}
