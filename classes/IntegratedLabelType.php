<?php

namespace Paperwork;

/**
 * The IntegratedLabelType class manages a collection of label configurations,
 * allowing for the retrieval of specific label measurements. This data is
 * intended to be used to style the paperwork page/s accordingly.
 */
class IntegratedLabelType
{
    /**
     * The available label options. The dimensions need to be formatted as css
     * values using mm as the unit of measurement. These presets can be
     * overridden using the constructor or the setLabelTypes() method.
     * @var array
     */
    private $labelTypes = [
        [
            'type' => 'priory-direct-single-up',
            'orientation' => 'landscape',
            'labels_per_page' => 1,
            'page_margin' => '8mm 10mm 10mm 10mm',
            'label_width' => '160mm',
            'label_height' => '105mm',
            'label_margin' => '0 0 5mm 15mm',
        ],
        [
            'type' => 'priory-direct-single-b',
            'orientation' => 'landscape',
            'labels_per_page' => 1,
            'page_margin' => '10mm',
            'label_width' => '110mm',
            'label_height' => '76mm',
            'label_margin' => '0 0 0 90mm',
        ],
        [
            'type' => 'priory-direct-single-r',
            'orientation' => 'landscape',
            'labels_per_page' => 1,
            'page_margin' => '10mm',
            'label_width' => '150mm',
            'label_height' => '100mm',
            'label_margin' => '0 0 0 40mm',
        ],
        [
            'type' => 'priory-direct-double-d',
            'orientation' => 'landscape',
            'labels_per_page' => 2,
            'page_margin' => '10mm',
            'label_width' => '95mm',
            'label_height' => '65mm',
            'label_margin' => '0',
        ],
        [
            'type' => 'half-sheet',
            'orientation' => 'portrait',
            'labels_per_page' => 2,
            'page_margin' => '10mm',
            'label_width' => '190mm',
            'label_height' => '128.5mm',
            'label_margin' => '0 0 10mm 0',
        ],
        [
            'type' => 'bespoke-01',
            'orientation' => 'landscape',
            'labels_per_page' => 1,
            'page_margin' => '10mm',
            'label_width' => '130mm',
            'label_height' => '75mm',
            'label_margin' => '35mm 0 0 65mm',
        ]
    ];

    /**
     * Constructor optionally sets the label types if different from the
     * presets.
     */
    public function __construct(array|null $labelTypes = null)
    {
        if(isset($labelTypes)){
            $this->setLabelTypes($labelTypes);
        }
    }

    /**
     * Sets the available label types if different from the presets.
     */
    public function setLabelTypes(array $labelTypes) : void
    {
        $this->labelTypes = $labelTypes;
    }

    /**
     * Returns the label type data for the given type. If no type is specified,
     * or if the specified type is not found, the default (first) label type
     * will be returned.
     */
    public function getLabelType(string|null $type = null) : array
    {
        if ($type === null) {
            return $this->labelTypes[0];
        }

        foreach ($this->labelTypes as $label) {
            if (strtoupper($label['type']) === strtoupper($type)) {
                return $label;
            }
        }

        return $this->labelTypes[0];
    }
}