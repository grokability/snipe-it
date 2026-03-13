<?php

namespace App\Models\Labels;

use App\Helpers\Helper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use TCPDF;
use TCPDF_STATIC;
use TypeError;
use Illuminate\Support\Facades\Log;

/**
 * Model for Labels.
 *
 * @version v1.0
 */
abstract class Label
{
    /**
     * Returns the unit of measure used
     * 'pt', 'mm', 'cm', 'in'
     * 
     * @return string
     */
    public abstract function getUnit();

    /**
     * Returns the PDF rotation.
     * 0, 90, 180, 270
     * 0 is a sane default. Override when necessary.
     *
     * @return int
     */
    public function getRotation()
    {
        return 0;
    }

    /**
     * Returns the label's width in getUnit() units
     * 
     * @return float
     */
    public abstract function getWidth();

    /**
     * Returns the label's height in getUnit() units
     * 
     * @return float
     */
    public abstract function getHeight();

    /**
     * Returns the label's top margin in getUnit() units
     * 
     * @return float
     */
    public abstract function getMarginTop();

    /**
     * Returns the label's bottom margin in getUnit() units
     * 
     * @return float
     */
    public abstract function getMarginBottom();

    /**
     * Returns the label's left margin in getUnit() units
     * 
     * @return float
     */
    public abstract function getMarginLeft();

    /**
     * Returns the label's right margin in getUnit() units
     * 
     * @return float
     */
    public abstract function getMarginRight();
    
    /**
     * Returns whether the template supports an asset tag.
     * 
     * @return bool
     */
    public abstract function getSupportAssetTag();
    
    /**
     * Returns whether the template supports a 1D barcode.
     * 
     * @return bool
     */
    public abstract function getSupport1DBarcode();
    
    /**
     * Returns whether the template supports a 2D barcode.
     * 
     * @return bool
     */
    public abstract function getSupport2DBarcode();
    
    /**
     * Returns the number of fields the template supports.
     * 
     * @return int
     */
    public abstract function getSupportFields();
    
    /**
     * Returns whether the template supports a logo.
     * 
     * @return bool
     */
    public abstract function getSupportLogo();
    
    /**
     * Returns whether the template supports a title.
     * 
     * @return bool
     */
    public abstract function getSupportTitle();

    /**
     * Make changes to the PDF properties here. OPTIONAL.
     * 
     * @param TCPDF $pdf The TCPDF instance
     */
    public abstract function preparePDF(TCPDF $pdf);

    /**
     * Write single data record as content here.
     * 
     * @param TCPDF      $pdf    The TCPDF instance
     * @param Collection $record A data record
     */
    public abstract function write(TCPDF $pdf, Collection $record);

    /**
     * Handle the data here. Override for multiple-per-page handling
     * 
     * @param TCPDF      $pdf  The TCPDF instance
     * @param Collection $data The data
     */
    public function writeAll(TCPDF $pdf, Collection $data)
    {
        $data->each(
            function ($record, $index) use ($pdf) {
                $pdf->AddPage();
                $this->write($pdf, $record);
            }
        );
    }

    /**
     * Returns the qualified class name relative to the Label class's namespace.
     *
     * @return string
     */
    public final function getName()
    {
        $refClass = new \ReflectionClass(Label::class);
        return str_replace($refClass->getNamespaceName() . '\\', '', get_class($this));
    }

    /**
     * Returns the label's orientation as a string.
     * 'L' = Landscape
     * 'P' = Portrait
     *
     * @return string
     */
    public final function getOrientation()
    {
        return ($this->getWidth() >= $this->getHeight()) ? 'L' : 'P';
    }

    /**
     * Returns the label's printable area as an object.
     *
     * @return object [ 'x1'=>0.00, 'y1'=>0.00, 'x2'=>0.00, 'y2'=>0.00, 'w'=>0.00, 'h'=>0.00 ]
     */
    public final function getPrintableArea()
    {
        return (object)[
            'x1' => $this->getMarginLeft(),
            'y1' => $this->getMarginTop(),
            'x2' => $this->getWidth() - $this->getMarginRight(),
            'y2' => $this->getHeight() - $this->getMarginBottom(),
            'w'  => $this->getWidth() - $this->getMarginLeft() - $this->getMarginRight(),
            'h'  => $this->getHeight() - $this->getMarginTop() - $this->getMarginBottom(),
        ];
    }

    /**
     * Write a text cell.
     * 
     * @param TCPDF  $pdf     The TCPDF instance
     * @param string $text    The text to write. Supports 'some **bold** text'.
     * @param float  $x       X position of top-left
     * @param float  $y       Y position of top-left
     * @param string $font    The font family
     * @param string $style   The font style
     * @param int    $size    The font size in getUnit() units
     * @param string $align   Align text in the box. 'L' left, 'R' right, 'C' center.
     * @param float  $width   Force text box width. NULL to auto-fit.
     * @param float  $height  Force text box height. NULL to auto-fit.
     * @param bool   $squash  Squash text if it's too big
     * @param int    $border  Thickness of border. Default = 0.
     * @param int    $spacing Letter spacing. Default = 0.
     */
    public final function writeText(TCPDF $pdf, $text, $x, $y, $font=null, $style=null, $size=null, $align='L', $width=null, $height=null, $squash=false, $border=0, $spacing=0)
    {


        $prevFamily = $pdf->getFontFamily();
        $prevStyle = $pdf->getFontStyle();
        $prevSizePt = $pdf->getFontSizePt();


        $text = !empty($text) ? $text : '';

        $fontFamily = !empty($font) ? $font : $prevFamily;
        $fontStyle = !empty($style) ? $style : $prevStyle;


        if ($size) {
            $fontSizePt = Helper::convertUnit($size, $this->getUnit(), 'pt', true);
        } else {
            $fontSizePt = $prevSizePt;
        }

        $pdf->SetFontSpacing($spacing);

        $parts = collect(explode('**', $text))
            ->map(
                function ($part, $index) use ($pdf, $fontFamily, $fontStyle, $fontSizePt, $text) {
                    $modStyle = ($index % 2 == 1) ? 'B' : $fontStyle;
                    $pdf->setFont($fontFamily, $modStyle, $fontSizePt);
                    return [
                    'text' => $part,
                    'text_width' => $pdf->GetStringWidth($part),
                    'font_family' => Helper::isCjk($text) ? 'cid0cs' : $fontFamily,
                    'font_style' => $modStyle,
                    'font_size' => $fontSizePt,
                    ];
                }
            );

        $textWidth = $parts->reduce(
            function ($carry, $part) {
                return $carry += $part['text_width']; 
            }
        );
        $cellWidth = !empty($width) ? $width : $textWidth;

        if ($squash && ($textWidth > 0)) {
            $scaleFactor = min(1.0, $cellWidth / $textWidth);
            $parts = $parts->map(
                function ($part, $index) use ($scaleFactor) {
                    $part['text_width'] = $part['text_width'] * $scaleFactor;
                    return $part;
                }
            );
        }
        $cellHeight = !empty($height) ? $height : Helper::convertUnit($fontSizePt, 'pt', $this->getUnit());

        if ($border) {
            $prevLineWidth = $pdf->getLineWidth();
            $pdf->setLineWidth($border);
            $pdf->Rect($x, $y, $cellWidth, $cellHeight);
            $pdf->setLineWidth($prevLineWidth);
        }

        switch($align) {
        case 'R': $startX = ($x + $cellWidth) - min($cellWidth, $textWidth); 
            break;
        case 'C': $startX = ($x + ($cellWidth / 2)) - (min($cellWidth, $textWidth) / 2); 
            break;
        case 'L':
        default: $startX = $x; 
            break;
        }

        $parts->reduce(
            function ($currentX, $part) use ($pdf, $y, $cellHeight) {
                $pdf->SetXY($currentX, $y);
                $pdf->setFont($part['font_family'], $part['font_style'], $part['font_size']);
                $pdf->Cell($part['text_width'], $cellHeight, $part['text'], 0, 0, '', false, '', 1, true);
                return $currentX += $part['text_width'];
            }, $startX
        );

        $pdf->SetFont($prevFamily, $prevStyle, $prevSizePt);
        $pdf->SetFontSpacing(0);
    }

    /**
     * Write an image.
     * 
     * @param TCPDF  $pdf     The TCPDF instance
     * @param string $image   The image to write
     * @param float  $x       X position of top-left
     * @param float  $y       Y position of top-left
     * @param float  $width   The container width
     * @param float  $height  The container height
     * @param string $halign  Align text in the box. 'L' left, 'R' right, 'C' center. Default 'L'.
     * @param string $valign  Align text in the box. 'T' top, 'B' bottom, 'C' center. Default 'T'.
     * @param int    $dpi     Pixels per inch
     * @param bool   $resize  Resize to fit container
     * @param bool   $stretch Stretch (vs Scale) to fit container
     * @param int    $border  Thickness of border. Default = 0.
     * 
     * @return array   Returns the final calculated size [w,h]
     */
    public final function writeImage(TCPDF $pdf, $image, $x, $y, $width=null, $height=null, $halign='L', $valign='L', $dpi=300, $resize=false, $stretch=false, $border=0)
    {

        if (empty($image)) { return [0,0];
        }
        
        $imageInfo = getimagesize($image);
        if (!$imageInfo) { return [0,0]; // TODO: SVG or other
        }
        
        $imageWidthPx = $imageInfo[0];
        $imageHeightPx = $imageInfo[1];
        $imageType = image_type_to_extension($imageInfo[2], false);

        $imageRatio = $imageWidthPx / $imageHeightPx;
        $dpu = Helper::convertUnit($dpi, $this->getUnit(), 'in');
        $imageWidth = $imageWidthPx / $dpu;
        $imageHeight = $imageHeightPx / $dpu;

        $outputWidth = $imageWidth;
        $outputHeight = $imageHeight;

        if ($resize) {
            // Assign specified parameters
            $limitWidth = $width;
            $limitHeight = $height;
            
            // If not, try calculating from the other dimension
            $limitWidth = ($limitWidth > 0) ? $limitWidth : ($limitHeight / $imageRatio);
            $limitHeight = ($limitHeight > 0) ? $limitHeight : ($limitWidth * $imageRatio);
            
            // If not, just use the image size
            $limitWidth = ($limitWidth > 0) ? $limitWidth : $imageWidth;
            $limitHeight = ($limitHeight > 0) ? $limitHeight : $imageHeight;

            $scaleWidth = $limitWidth / $imageWidth;
            $scaleHeight = $limitHeight / $imageHeight;

            // If non-stretch, make both scales factors equal
            if (!$stretch) {
                // Do we need to scale down at all? That's most important.
                if (($scaleWidth < 1.0) || ($scaleHeight < 1.0)) {
                    // Choose largest scale-down
                    $scaleWidth = min($scaleWidth, $scaleHeight);
                } else {
                    // Choose smallest scale-up
                    $scaleWidth = min(max($scaleWidth, 1.0), max($scaleHeight, 1.0));
                }
                $scaleHeight = $scaleWidth;
            }

            $outputWidth = $imageWidth * $scaleWidth;
            $outputHeight = $imageHeight * $scaleHeight;
        }

        // Container
        $containerWidth = !empty($width) ? $width : $outputWidth;
        $containerHeight = !empty($height) ? $height : $outputHeight;

        // Horizontal Position
        switch ($halign) {
        case 'R': $originX = ($x + $containerWidth) - $outputWidth; 
            break;
        case 'C': $originX = ($x + ($containerWidth / 2)) - ($outputWidth / 2); 
            break;
        case 'L':
        default: $originX = $x; 
            break;
        }
        
        // Vertical Position
        switch ($valign) {
        case 'B': $originY = ($y + $containerHeight) - $outputHeight; 
            break;
        case 'C': $originY = ($y + ($containerHeight / 2)) - ($outputHeight / 2); 
            break;
        case 'T':
        default: $originY = $y; 
            break;
        }

        // Actual Image
        $pdf->Image($image, $originX, $originY, $outputWidth, $outputHeight, $imageType, '', '', true);
        
        // Border
        if ($border) {
            $prevLineWidth = $pdf->getLineWidth();
            $pdf->setLineWidth($border);
            $pdf->Rect($x, $y, $containerWidth, $containerHeight);
            $pdf->setLineWidth($prevLineWidth);
        }
        
        return [ $outputWidth, $outputHeight ];
    }

    /**
     * Write a 1D barcode.
     * 
     * @param TCPDF  $pdf    The TCPDF instance
     * @param string $value  The barcode content
     * @param string $type   The barcode type
     * @param float  $x      X position of top-left
     * @param float  $y      Y position of top-left
     * @param float  $width  The container width
     * @param float  $height The container height
     */
    public final function write1DBarcode(TCPDF $pdf, $value, $type, $x, $y, $width, $height)
    {
        if (empty($value)) { return;
        }
        try {
            $pdf->write1DBarcode($value, $type, $x, $y, $width, $height, null, ['stretch'=>true]);
        } catch (\Exception|TypeError $e) {
            Log::debug('The 1D barcode ' . $value . ' is not compliant with the barcode type '. $type);
        }
    }

    /**
     * Write a 2D barcode.
     * 
     * @param TCPDF  $pdf    The TCPDF instance
     * @param string $value  The barcode content
     * @param string $type   The barcode type
     * @param float  $x      X position of top-left
     * @param float  $y      Y position of top-left
     * @param float  $width  The container width
     * @param float  $height The container height
     */
    public final function write2DBarcode(TCPDF $pdf, $value, $type, $x, $y, $width, $height)
    {
        if (empty($value)) { return;
        }
        $pdf->write2DBarcode($value, $type, $x, $y, $width, $height, null, ['stretch'=>true]);
    }



    /**
     * Checks the template is internally valid
     */
    public final function validate() : void
    {
        $this->validateUnits();
        $this->validateSize();
        $this->validateMargins();
        $this->validateSupport();
    }

    private function validateUnits() : void
    {
        $validUnits = [ 'pt', 'mm', 'cm', 'in' ];
        $unit = $this->getUnit();
        if (!in_array(strtolower($unit), $validUnits)) {
            throw new \UnexpectedValueException(
                trans(
                    'admin/labels/message.invalid_return_value', [
                    'name' => 'getUnit()',
                    'expected' => '[ \''.implode('\', \'', $validUnits).'\' ]',
                    'actual' => '\''.$unit.'\''
                    ]
                )
            );
        }
    }

    private function validateSize() : void
    {
        $width = $this->getWidth();
        if (!is_numeric($width) || is_string($width)) {
            throw new \UnexpectedValueException(
                trans(
                    'admin/labels/message.invalid_return_type', [
                    'name' => 'getWidth()',
                    'expected' => 'float',
                    'actual' => gettype($width)
                    ]
                )
            );
        }
        
        $height = $this->getHeight();
        if (!is_numeric($height) || is_string($height)) {
            throw new \UnexpectedValueException(
                trans(
                    'admin/labels/message.invalid_return_type', [
                    'name' => 'getHeight()',
                    'expected' => 'float',
                    'actual' => gettype($height)
                    ]
                )
            );
        }
    }

    private function validateMargins() : void
    {
        $marginTop = $this->getMarginTop();
        if (!is_numeric($marginTop) || is_string($marginTop)) {
            throw new \UnexpectedValueException(
                trans(
                    'admin/labels/message.invalid_return_type', [
                    'name' => 'getMarginTop()',
                    'expected' => 'float',
                    'actual' => gettype($marginTop)
                    ]
                )
            );
        }
        
        $marginBottom = $this->getMarginBottom();
        if (!is_numeric($marginBottom) || is_string($marginBottom)) {
            throw new \UnexpectedValueException(
                trans(
                    'admin/labels/message.invalid_return_type', [
                    'name' => 'getMarginBottom()',
                    'expected' => 'float',
                    'actual' => gettype($marginBottom)
                    ]
                )
            );
        }

        $marginLeft = $this->getMarginLeft();
        if (!is_numeric($marginLeft) || is_string($marginLeft)) {
            throw new \UnexpectedValueException(
                trans(
                    'admin/labels/message.invalid_return_type', [
                    'name' => 'getMarginLeft()',
                    'expected' => 'float',
                    'actual' => gettype($marginLeft)
                    ]
                )
            );
        }
        
        $marginRight = $this->getMarginRight();
        if (!is_numeric($marginRight) || is_string($marginRight)) {
            throw new \UnexpectedValueException(
                trans(
                    'admin/labels/message.invalid_return_type', [
                    'name' => 'getMarginRight()',
                    'expected' => 'float',
                    'actual' => gettype($marginRight)
                    ]
                )
            );
        }
    }

    private function validateSupport() : void
    {
        $support1D = $this->getSupport1DBarcode();
        if (!is_bool($support1D)) {
            throw new \UnexpectedValueException(
                trans(
                    'admin/labels/message.invalid_return_type', [
                    'name' => 'getSupport1DBarcode()',
                    'expected' => 'boolean',
                    'actual' => gettype($support1D)
                    ]
                )
            );
        }

        $support2D = $this->getSupport2DBarcode();
        if (!is_bool($support2D)) {
            throw new \UnexpectedValueException(
                trans(
                    'admin/labels/message.invalid_return_type', [
                    'name' => 'getSupport2DBarcode()',
                    'expected' => 'boolean',
                    'actual' => gettype($support2D)
                    ]
                )
            );
        }

        $supportFields = $this->getSupportFields();
        if (!is_int($supportFields)) {
            throw new \UnexpectedValueException(
                trans(
                    'admin/labels/message.invalid_return_type', [
                    'name' => 'getSupportFields()',
                    'expected' => 'integer',
                    'actual' => gettype($supportFields)
                    ]
                )
            );
        }

        $supportLogo = $this->getSupportLogo();
        if (!is_bool($supportLogo)) {
            throw new \UnexpectedValueException(
                trans(
                    'admin/labels/message.invalid_return_type', [
                    'name' => 'getSupportLogo()',
                    'expected' => 'boolean',
                    'actual' => gettype($supportLogo)
                    ]
                )
            );
        }

        $supportTitle = $this->getSupportTitle();
        if (!is_bool($supportTitle)) {
            throw new \UnexpectedValueException(
                trans(
                    'admin/labels/message.invalid_return_type', [
                    'name' => 'getSupportTitle()',
                    'expected' => 'boolean',
                    'actual' => gettype($supportTitle)
                    ]
                )
            );
        }
    }



    /**
     * Public Static Functions
     */

    /**
     * Find size of a page by its format.
     * 
     * @param string $format      Format name (eg: 'A4', 'LETTER', etc.)
     * @param string $orientation 'L' for Landscape, 'P' for Portrait ('L' default)
     * @param string $unit        Unit of measure to return in ('mm' default)
     * 
     * @return object  (object)[ 'width' => (float)123.4, 'height' => (float)123.4 ]
     */
    public static function fromFormat($format, $orientation='L', $unit='mm', $round=false)
    {
        $size = collect(TCPDF_STATIC::getPageSizeFromFormat(strtoupper($format)))
            ->sort()
            ->map(
                function ($value) use ($unit) {
                    return Helper::convertUnit($value, 'pt', $unit);
                }
            )
            ->toArray();
        $width = ($orientation == 'L') ? $size[1] : $size[0];
        $height = ($orientation == 'L') ? $size[0] : $size[1];
        return (object)[
            'width'  => ($round !== false) ? round($width, $round)  : $width,
            'height' => ($round !== false) ? round($height, $round) : $height,
        ];
    }

    /**
     * Find a Label by its path (or just return them all).
     * 
     * Unlike most Models, these are defined by their existence as non-
     * abstract classes stored in Models\Labels.
     *
     * @param  string|Arrayable|array|null $path Label path[s]
     * @return Collection|Label|null
     */
    public static function find($name=null)
    {
        // Find many
        if (is_array($name) || $name instanceof Arrayable) {
            $labels = collect($name)
                ->map(
                    function ($thisname) {
                        return static::find($thisname);
                    }
                )
                ->whereNotNull();
            return ($labels->count() > 0) ? $labels : null;
        }

        // Find one
        if ($name !== null) {
            return static::find()
                ->sole(
                    function ($label) use ($name) {
                        return $label->getName() == $name;
                    }
                );
        }

        // Find all
        return collect(File::allFiles(__DIR__))
            ->map(
                function ($file) {
                    preg_match_all('/\/*(.+?)(?:\/|\.)/', $file->getRelativePathName(), $matches);
                    return __NAMESPACE__ . '\\' . implode('\\', $matches[1]);
                }
            )
            ->filter(
                function ($name) {
                    if (!class_exists($name)) { return false;
                    }
                    $refClass = new \ReflectionClass($name);
                    if ($refClass->isAbstract()) { return false;
                    }
                    return $refClass->isSubclassOf(Label::class);
                }
            )
            ->map(
                function ($name) {
                    return new $name();
                }
            );
    }

    

}
