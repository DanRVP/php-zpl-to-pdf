<?php

declare(strict_types=1);

namespace PhpZpl;

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

    public function toPdf()
    {

    }

    private function parseZpl()
    {
        $parser = new Parser(new Lexer($this->input_stream));
        $this->input_stream->close();
        return $parser->parse();
    }
}
