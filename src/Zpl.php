<?php

declare(strict_types=1);

namespace PhpZpl;

use Exception;

class Zpl
{
    /**
     * Construct a new Zpl instance
     *
     * @param Stream $input_stream Should be a resource stream
     */
    public function __construct(private Stream $input_stream)
    {
    }

    /**
     * Convert the ZPL to PDF
     *
     * @return string A PDF string
     */
    public function toPdf(): string
    {
        $lexer = new Lexer($this->input_stream);
        $parser = new Parser($lexer);
        $commands = $parser->parse();

        $first = array_shift($commands)[0] ?? null;
        if ($first !== 'XA') {
            throw new Exception('Valid ZPLs must start with the "^XA" command');
        }

        $last = array_pop($commands)[0] ?? null;
        if ($last !== 'XZ') {
            throw new Exception('Valid ZPLs must end with the "^XZ" command');
        }

        $converter = new Converter();
        foreach ($commands as $command) {
            call_user_func_array([$converter, $command[0]], $command[1]);
        }

        return $converter->getPdfString();
    }
}
