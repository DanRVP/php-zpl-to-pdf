<?php

declare(strict_types=1);

namespace PhpZpl;

use Exception;
use Fpdf\Fpdf;

class Converter
{
    /**
     * FPDF instance to build PDF with
     *
     * @var Fpdf
     */
    private Fpdf $pdf;

    /**
     * Home value on the x axis in mm.
     * Overwritten by ^LH.
     *
     * @var int
     */
    private int $home_x = 0;

    /**
     * Home value on the y axis in mm.
     * Overwritten by ^LH.
     *
     * @var int
     */
    private int $home_y = 0;

    /**
     * Construct a new instance of the Converter
     *
     * @param int $dpmm The dots per millimeter to render the ZPL with
     */
    public function __construct(private int $dpmm = 8)
    {
        $this->pdf = new Fpdf('P', 'mm', [101.6, 152.4]);
        $this->pdf->AddPage();
        $this->pdf->SetFont('Arial', 'B', 16);
    }

    /**
     * Get the value of pdf
     */
    public function getPdfString(): string
    {
        return $this->pdf->Output('S');
    }

    public function __call($name, $arguments)
    {
        // Use call to prevent bad method call exceptions when testing
        return $this;
    }

    /**
     * Convert dot values to mm values
     *
     * @param int $dots Number of dots
     * @return int|float
     */
    private function dotsToMm(int $dots): int|float
    {
        return $dots / $this->dpmm;
    }

    /******************************************************************************************************************
     ********************************************* ZPL COMMANDS *******************************************************
     ******************************************************************************************************************/

    /**
     * The ^A command specifies the font to use in a text field. ^A designates the font for the current ^FD
     * statement or field. The font specified by ^A is used only once for that ^FD entry. If a value for ^A is not
     * specified again, the default ^CF font is used for the next ^FD entry.
     *
     * @return void
     */
    public function A(): self
    {
        // TODO: Implement fonts
        return $this;
    }

    /**
     * The ^A@ command uses the complete name of a font, rather than the character designation used in ^A.
     * Once a value for ^A@ is defined, it represents that font until a new font name is specified by ^A@.
     *
     * @return self
     */
    public function AAT(): self
    {
        // TODO: Implement fonts
        return $this;
    }

    /**
     * The ^FD command defines the data string for a field. The field data can be any printable character except
     * those used as command prefixes (^ and ~).
     *
     * @param array $strings
     * @return self
     */
    public function FD(...$strings): self
    {
        // The parser is dumb and just runs explode on all things following a command.
        // As FD only takes 1 argument we'll just re-implode this to stitch the original string back together.
        $string = implode(',', $strings);
        $this->pdf->Cell(0, 0, $string);
        return $this;
    }

    /**
     * The ^FS command denotes the end of the field definition.
     */
    public function FS(): self
    {
        // TODO: Clear field font set by ^A
        return $this;
    }

    /**
     * The ^FO command sets a field origin, relative to the label home (^LH) position. ^FO sets the upper-left
     * corner of the field area by defining points along the x-axis and y-axis independent of the rotation.
     *
     * @param int $x The position on the x axis to position the field
     * @param int $y The position on the y axis to position the field
     */
    public function FO(int $x, int $y): self
    {
        if ($x < 0 || $x > 32000) {
            throw new Exception('FO: x must be between 0 and 32000');
        }

        if ($y < 0 || $y > 32000) {
            throw new Exception('FO: y must be between 0 and 32000');
        }

        $this->pdf->SetXY($this->home_x + $this->dotsToMm($x), $this->home_y + $this->dotsToMm($y));
        return $this;
    }
}
