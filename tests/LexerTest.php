<?php

declare(strict_types=1);

namespace PhpZpl\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use PhpZpl\Lexer;
use PhpZpl\Stream;

class LexerTest extends TestCase
{
    #[DataProvider('readNextProvider')]
    public function testReadNext(Stream $stream)
    {
        $lexer = new Lexer($stream);
        while ($result = $lexer->readNext()) {
            $results[] = $result;
        }

        $this->assertEquals([
            '^XA',
            '^FO50,60',
            '^A0,40',
            '^FDWorld\'s Best Griddle',
            '^FS',
            '^FO60,120',
            '^BY3',
            '^BCN,60,,,,A',
            '^FD1234ABC',
            '^FS',
            '^FO25,25',
            '^GB380,200,2',
            '^FS',
            '^XZ'
        ], $results);
    }

    /**
     * Provider for testReadNext
     *
     * @return array
     */
    public static function readNextProvider(): array
    {
        $test_data = "^XA
            ^FO50,60^A0,40^FDWorld's Best Griddle^FS
            ^FO60,120^BY3^BCN,60,,,,A^FD1234ABC^FS
            ^FO25,25^GB380,200,2^FS
            ^XZ";

        $resource = fopen('php://temp', 'rw');
        fputs($resource, $test_data);
        rewind($resource);
        $test = [new Stream($resource)];

        return compact('test');
    }
}
