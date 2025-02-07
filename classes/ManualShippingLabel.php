<?php

namespace Paperwork;

use GdImage;
use Picqer\Barcode\BarcodeGeneratorPNG;

/**
 * Plateit handles creating shipments with third-party shipping providers which
 * create their own labels which are referenced in the Twig templates. However,
 * to help process manual shipments (I.E taking to the Post Office in person) we
 * will need to generate custom, manual labels. This class takes care of this.
 */
class ManualShippingLabel
{
    /**
     * The shipping address. @var array
     */
    private $shipToAddress;

    /**
     * The data for the optional barcode. @var int|string
     */
    private $barcodeData;

    /**
     * Default label options. Can be overidden using the constructor or the
     * setOptions() method. @var array
     */
    private $options = [
        'hasTransparentBg'  => true,
        'width'             => 1200,
        'height'            => 800,
        'fontFile'          => 'fonts/OpenSans-Medium.ttf',
        'fontSize'          => 35,
        'lineSpacing'       => 15,
        'x'                 => 100,
        'y'                 => 125,
        'barCodeWidthFactor'=> 6,
        'barCodeHeight'     => 100,
    ];

    /**
     * The keys to utilise from the shipping address. @var array
     */
    private $addressKeys = [
        'first_name',
        'last_name',
        'address_line_1',
        'address_line_2',
        'address_line_3',
        'address_postcode',
        'address_country_code'
    ];

    /**
     * Constructor optionally sets the shipping address, barcode data and label
     * options.
     */
    public function __construct(array $shipToAddress = null, int|string $barcodeData = null, array $options = null)
    {
        if($shipToAddress){
            $this->setShipToAddress($shipToAddress);
        }
        if($barcodeData){
            $this->setBarcodeData($barcodeData);
        }
        if($options){
            $this->setOptions($options);
        }
    }

    /**
     * Sets the label's shipping address.
     */
    public function setShipToAddress(array $shipToAddress) : self
    {
        $this->shipToAddress = $shipToAddress;
        return $this;
    }

    /**
     * Sets the label's barcode data.
     */
    public function setBarcodeData(string|int $barcodeData) : self
    {
        $this->barcodeData = $barcodeData;
        return $this;
    }

    /**
     * Sets custom label options.
     */
    public function setOptions(array $newOptions) : void
    {
        foreach ($newOptions as $key => $value) {
            if (
                array_key_exists($key, $this->options) &&
                (
                    is_int($value) ||
                    is_bool($value) ||
                    is_string($value)
                )
            ) {
                $this->options[$key] = $value;
            }
        }
    }

    /**
     * Generates the label and returns a src-ready, base64 png image string.
     */
    public function generateLabel(): string
    {
        $image = $this->createBaseImage();
        
        $this->addAddress($image);

        if(!empty($this->barcodeData)){
            $this->addBarcode($image);
        }

        ob_start();
        imagepng($image);
        $imageData = ob_get_contents();
        ob_end_clean();
        imagedestroy($image);
        
        return 'data:image/png;base64,' . base64_encode($imageData);
    }

    /**
     * Creates the GD library label base image.
     */
    private function createBaseImage() : GdImage
    {
        $width = $this->options['width'];
        $height = $this->options['height'];
        
        $image = imagecreatetruecolor($width, $height);
        imagesavealpha($image, true);

        $backgroundColor = $this->options['hasTransparentBg'] ?
            imagecolorallocatealpha($image, 0, 0, 0, 127) : // Transparent.
            imagecolorallocate($image, 255, 255, 0); // Yellow BG for debugging.

        imagefill($image, 0, 0, $backgroundColor);
        return $image;
    }

    /**
     * Adds the shipping address text to the GD image.
     */
    private function addAddress(GdImage $image)
    {
        $black = imagecolorallocate($image, 0, 0, 0);
        $x = $this->options['x'];
        $y = $this->options['y'];
        $fontSize = $this->options['fontSize'];
        $fontFile = $this->options['fontFile'];
        $lineSpacing = $this->options['lineSpacing'];

        foreach ($this->addressKeys as $key) {
            $value = $this->shipToAddress[$key] ?? '';

            // Join the first and last names together on a single line.
            if ($key === 'first_name') {
                $firstName = $value;
                $lastName = $this->shipToAddress['last_name'] ?? '';
                $value = $firstName . ' ' . $lastName;
            } elseif ($key === 'last_name') {
                continue;
            }

            if (is_string($value) && !empty($value)) {
                imagettftext($image, $fontSize, 0, $x, $y, $black, $fontFile, trim($value));
                $y += $fontSize + $lineSpacing;
            }
        }
    }

    /**
     * Adds the barcode to the GD image.
     */
    private function addBarcode(GdImage $image) : void
    {
        $generator = new BarcodeGeneratorPNG();
        
        $barcodeData = $generator->getBarcode(
            $this->barcodeData,
            $generator::TYPE_CODE_128,
            $this->options['barCodeWidthFactor'],
            $this->options['barCodeHeight']
        );
        
        // Create a GD image from the barcode.
        $barcodeImage = imagecreatefromstring($barcodeData);
        $barcodeWidth = imagesx($barcodeImage);
        $barcodeHeight = imagesy($barcodeImage);
        
        // Position the barcode on the label.
        $addressLines = count($this->addressKeys);
        $barcodeX = $this->options['x'];
        $barcodeY = $this->options['y'] + ($addressLines * ($this->options['fontSize'] + 15)) + 50;

        // Merge the barcode image onto the label image.
        imagecopyresampled($image, $barcodeImage, $barcodeX, $barcodeY, 0, 0, $barcodeWidth, $barcodeHeight, $barcodeWidth, $barcodeHeight);
        
        // Clean up.
        imagedestroy($barcodeImage);
    }
}
