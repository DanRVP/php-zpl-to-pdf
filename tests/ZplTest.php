<?php

declare(strict_types=1);

namespace PhpZpl\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use PhpZpl\ZplFactory;

class ZplTest extends TestCase
{
    #[DataProvider('readNextProvider')]
    public function testToPdf($zpl_string)
    {
        $zpl = ZplFactory::fromString($zpl_string);
        $result = $zpl->toPdf(101.6, 152.4);
        file_put_contents('test.pdf', $result);
    }

    /**
     * Provider for testReadNext
     *
     * @return array
     */
    public static function readNextProvider(): array
    {
        $basic = [
            'zpl_string' => "^XA
                ^FO50,60^A0,40^FDWorld's Best Griddle^FS
                ^FO60,120^BY3^BCN,60,,,,A^FD1234ABC^FS
                ^FO25,25^GB380,200,2^FS
                ^XZ",
        ];

        return compact('basic');
    }
}
