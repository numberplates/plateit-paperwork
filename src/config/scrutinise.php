<?php

/**
 * Default fields to compare when plates share a registration, width and height.
 *
 * Front and rear plates usually have matching designs. If a configured value
 * differs (for example, border thickness), Scrutinise flags it for staff so
 * the plates are manufactured with the correct designs on the correct sides.
 *
 * Company-specific design objects may use different structures. To customise
 * this list, return a mapping from overrides/companies/<id>/scrutinise.php, or
 * from overrides/shared/scrutinise.php for all companies.
 * 
 * An empty mapping disables comparisons.
 *
 * @return array<string, string>
 */
return [
    // Quantity.
    'qty'                   => 'qty',
    // Registration.
    'registration colour'   => 'design_object.reg.textColour',
    'registration font'     => 'design_object.reg.textFontUrl',
    // Border.
    'border colour'         => 'design_object.border.colour',
    'border thickness'      => 'design_object.border.thickness',
    // Bottom line.
    'bottom line text'      => 'design_object.bottomLine.text',
    'bottom line size'      => 'design_object.bottomLine.textHeight',
    'bottom line font'      => 'design_object.bottomLine.textFontUrl',
    'bottom line colour'    => 'design_object.bottomLine.textColour',
    // Side badge left.
    'left side badge image' => 'design_object.sideBadgeLeft.imageUrl',
    'left side badge text'  => 'design_object.sideBadgeLeft.text',
    'left side badge colour'=> 'design_object.sideBadgeLeft.backgroundColour',
    // Side badge right.
    'right side badge image' => 'design_object.sideBadgeRight.imageUrl',
    'right side badge text'  => 'design_object.sideBadgeRight.text',
    'right side badge colour'=> 'design_object.sideBadgeRight.backgroundColour'
];
