<?php

declare(strict_types=1);

namespace PhpZpl;

use Exception;
use Fpdf\Fpdf;

class Converter
{
    /**
     * 1mm === 2.8346456693 point (Used for font scaling)
     */
    private const MM_TO_POINTS_SCALE = 2.8346456693;

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
     * @var float
     */
    private float $home_x = 0;

    /**
     * Home value on the y axis in mm.
     * Overwritten by ^LH.
     *
     * @var float
     */
    private float $home_y = 0;

    /**
     * The current x axis coordinate in mm.
     *
     * @var float
     */
    private float $current_field_x = 0;

    /**
     * The current y axis coordinate in mm.
     *
     * @var float
     */
    private float $current_field_y = 0;

    /**
     * Flag to tell us if we are in a barcode. If we are generating a barcode, then the next call to ^FD will fill
     * barcode content and the next call to ^FS will render the barcode on the PDF.
     *
     * @var boolean
     */
    private bool $in_barcode = false;

    /**
     * Content derived from ^FD to use to generate a barcode
     *
     * @var string
     */
    private string $barcode_content = '';

    /**
     * Formatting of a barcode to be rendered
     *
     * @var array
     */
    private array $barcode_formatting = [];

    /**
     * Construct a new instance of the Converter
     *
     * @param float $width PDF width in mm
     * @param float $height PDF height in mm
     * @param int $dpmm The dots per millimeter to render the ZPL with
     */
    public function __construct(private float $width, private float $height, private int $dpmm = 8)
    {
        $this->pdf = new Fpdf('P', 'mm', [$width, $height]);
        $this->pdf->AddPage();
        $this->pdf->SetCreator('php-zpl-to-pdf', true);
        $this->defaultFont();
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
        // Use call to prevent bad method call exceptions while implementing new methods in development
    }

    /**
     * Convert dot values to mm values
     *
     * @param int $dots Number of dots
     * @return int|float
     */
    private function dotToMm(int $dots): int|float
    {
        return $dots / $this->dpmm;
    }

    /**
     * Convert mm values to point values
     *
     * @param float $mm Millimeter value
     * @return float
     */
    private function mmToPoint(float $mm): float
    {
        return $mm * static::MM_TO_POINTS_SCALE;
    }

    /**
     * Convenience function for setting the current doc's X and Y coordinates
     *
     * @param int $x
     * @param int $y
     * @return void
     */
    private function setXY(int $x, int $y): void
    {
        $this->current_field_x = $this->home_x + $this->dotToMm($x);
        $this->current_field_y = $this->home_y + $this->dotToMm($y);
        $this->pdf->SetXY($this->current_field_x, $this->current_field_x);
    }

    /**
     * Set the current PDF font to default
     *
     * @return void
     */
    private function defaultFont(): void
    {
        $this->pdf->SetFont('Arial', 'B', 12);
    }

    /**
     * Consume the currently stored barcode information and render it on the PDF
     *
     * @return void
     */
    private function renderBarcode(): void
    {
        // TODO: Render the barcode
        // Dummy render a barcode for spacing while testing
        $this->pdf->Cell(35, 15,'BARCODE', 1, 2, 'C');

        $this->in_barcode = false;
        $this->barcode_content = '';
        $this->barcode_formatting = [];

        return;
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
    public function A(int|string $f, ?int $h = null, ?int $w = null): self
    {
        // FDPF does not support widths + heights of a font, but instead works in points.
        // We'll use height and fall back to width if height is omitted.
        if ($h === null && $w === null) {
            // Defaults passed. Set height to minumum size of 10 dots
            $h = 10;
        }

        if ($h < 10 || $h > 32000) {
            throw new Exception('A: h must be greater than or equal to 10 and less than or equal to 32000');
        }

        if (!empty($w)) {
            if ($w < 10 || $w > 32000) {
                throw new Exception('A: w must be greater than or equal to 10 and less than or equal to 32000');
            }

            $font_size = $this->mmToPoint(($this->dotToMm($w)));
        } else {
            $font_size = $this->mmToPoint($this->dotToMm($h));
        }

        $this->pdf->SetFontSize($font_size);

        // TODO: Implement font styles
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
     * The ^BC command creates the Code 128 barcode, a high-density, variable length, continuous,
     * alphanumeric symbology. It was designed for complexly encoded product identification.
     *
     * @param string $o
     * @param integer $h Barcode height in dots
     * @param string $f Print interpretation line above barcode (Y/N)
     * @param string $g Print interpretation line below barcode (Y/N)
     * @param string $e Enable UCC check digit (Y/N)
     * @param string $m The mode set (N/U/A/D)
     * @return self
     */
    public function BC(?string $o = null, ?int $h = null, string $f, string $g = 'Y', string $e = 'N', string $m = 'N'): self
    {
        $this->in_barcode = true;
        $this->barcode_formatting = func_get_args();

        // TODO: Derive orientation from ^FW value if omitted in args
        // TODO: Derive barcode height from ^BY value if omitted in args
        // TODO: Work out what an interpretation line is
        // TODO: Implement modes
        // TODO: Render actual barcode

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
        if ($this->in_barcode) {
            $this->barcode_content = $string;
        } else {
            $this->pdf->Cell(0, 0, $string);
        }

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
            throw new Exception('FO: x must be greater than or equal to 0 and less than or equal to 32000');
        }

        if ($y < 0 || $y > 32000) {
            throw new Exception('FO: y must be greater than or equal to 0 and less than or equal to 32000');
        }

        $this->setXY($x, $y);
        return $this;
    }

    /**
     * The ^FS command denotes the end of the field definition.
     */
    public function FS(): self
    {
        if ($this->in_barcode) {
            $this->renderBarcode();
        }

        $this->defaultFont();
        $this->setXY(0, 0);

        // TODO: Clear field font set by ^A
        return $this;
    }

    /**
     * The ^GB command is used to draw boxes and lines as part of a label format. Boxes and lines are used to
     * highlight important information, divide labels into distinct areas, or improve the appearance of a label. The
     * same format command is used for drawing either boxes or lines.
     *
     * @param int $w The width of the box in dots
     * @param int $h The height of the box in dots
     * @param int $t The border thickness of the box being drawn
     * @param string $c The line color. Can be `B` (black) or `W` (white)
     * @param
     * @return self
     */
    public function GB(int $w, int $h, int $t = 1, string $c = 'B', int $r = 0): self
    {
        if ($w < $t || $w > 32000) {
            throw new Exception('GB: Width (w) must be greater than or equal to border thickness (t) and less than or equal to 32000');
        }

        if ($h < $t || $h > 32000) {
            throw new Exception('GB: Width (w) must be greater than or equal to border thickness (t) and less than or equal to 32000');
        }

        if ($c !== 'B' && $c !== 'W') {
            throw new Exception('GB: Colour (c) must be one of "B" or "W"');
        }

        if ($r < 0 || $r > 8) {
            throw new Exception('GB: Border radius (r) must be greater than or equal to 0 and less than or equal to 8');
        }

        $colour = [0, 0, 0];
        if ($c === 'W') {
            $colour = [255, 255, 255];
        }

        $this->pdf->SetDrawColor(...$colour);
        $this->pdf->SetLineWidth($this->dotToMm($t));
        $this->pdf->Rect(
            $this->current_field_x,
            $this->current_field_y,
            $this->dotToMm($w),
            $this->dotToMm($h),
        );

        return $this;
    }
}
