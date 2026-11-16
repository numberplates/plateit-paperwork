<?php

/**
 * Installation-wide integrated label settings; dimensions use CSS millimetres.
 * The default is used when ?type= is absent or unrecognised. A URL selection
 * applies to the entire batch, matching the paper loaded in the printer.
 *
 * overrides/shared/labels.php may return a default_type and/or types list.
 * A supplied types list replaces all bundled presets; include the default type.
 * Company-specific label configuration is intentionally not supported.
 *
 * @return array{default_type: string, types: array<int, array<string, mixed>>}
 */
return [
    'default_type' => 'priory-direct-single-up',
    'types' => [
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
],
];
