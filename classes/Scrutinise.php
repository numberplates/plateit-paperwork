<?php

namespace Paperwork;

/**
 * When a pair of front and rear number plates is purchased with matching
 * registrations and dimensions, they typically share identical designs.
 * However, there may be cases where the front plate differs from the rear
 * plate; for example, the front plate might have a red border, while its rear 
 * counterpart may have a blue border. In such instances, it is essential for
 * the paperwork to clearly indicate which plate is which to prevent incorrect
 * plates from being sent to the customer.
 * 
 * This class processes an array of purchased number plates. It identifies any
 * pairs (or more) of same-size plates that have minor design differences. For
 * such plates, the class appends a "scrutinise" attribute that specifies the
 * design elements requiring double-checking before manufacturing.
 */
class Scrutinise
{
    /**
     * @var array
     */
    private $plates = [];

    /**
     * The default plate attributes to scrutinise. Can be overridden in the
     * constructor or by using the setScrutinise() method. This is not an
     * exhaustive list but should cover 99% of use cases.
     * @var array
     */
    private $toScrutinise = [
        // Quantity
        'qty'                   => 'qty',
        // Registration
        'registration colour'   => 'design_metadata.reg.textColour',
        'registration font'     => 'design_metadata.reg.textFontUrl',
        // Border
        'border colour'         => 'design_metadata.border.colour',
        'border thickness'      => 'design_metadata.border.thickness',
        // Bottom line
        'bottom line text'      => 'design_metadata.bottomLine.text',
        'bottom line size'      => 'design_metadata.bottomLine.textHeight',
        'bottom line font'      => 'design_metadata.bottomLine.textFontUrl',
        'bottom line colour'    => 'design_metadata.bottomLine.textColour',
        // Side badge left
        'left side badge image' => 'design_metadata.sideBadgeLeft.imageUrl',
        'left side badge text'  => 'design_metadata.sideBadgeLeft.text',
        'left side badge colour'=> 'design_metadata.sideBadgeLeft.backgroundColour',
        // Side badge right
        'right side badge image' => 'design_metadata.sideBadgeRight.imageUrl',
        'right side badge text'  => 'design_metadata.sideBadgeRight.text',
        'right side badge colour'=> 'design_metadata.sideBadgeRight.backgroundColour'
    ];

    /**
     * Constructor optionally sets the array of plates and scrutinisation
     * instructions.
     */
    public function __construct(array $plates = null, array $toScrutinise = null)
    {
        if($plates){
            $this->setPlates($plates);
        }
        if ($toScrutinise) {
            $this->setScrutinise($toScrutinise);
        }
    }

    /**
     * Sets the plates array.
     */
    public function setPlates(array $plates) : void
    {
        $this->plates = $plates;
    }

    /**
     * Sets the scrutinisation instructions.
     */
    public function setScrutinise(array $toScrutinise) : void
    {
        $this->toScrutinise = $toScrutinise;
    }

    /**
     * Returns the modified plates array with appended "scrutinise" attributes
     * if design differences are detected pertaining to same-reg, same-dimension
     * plates.
     */
    public function getScrutinisedPlates() : array
    {
        $platesModified = $this->plates;
        
        foreach($this->plates as $i => $thisPlate){
            foreach($this->plates as $otherPlate){

                // Skip this plate if its reg and dimensions don't match the
                // other plate.
                if(
                    $thisPlate['id'] === $otherPlate['id'] ||
                    !$this->platesMatchRegAndSize($thisPlate, $otherPlate)
                ){
                    continue;
                }

                // Find any pertinent design differences between the two
                // plates and skip if there are none.
                $toScrutinise = $this->findElementsToScrutinise($thisPlate, $otherPlate);

                if(empty($toScrutinise)){
                    continue;
                }

                // If we get this far, pertinient differences have been found.
                // Add a "scrutinise" attribute.
                $platesModified[$i]['scrutinise'] = $toScrutinise;

            }
        }

        return $platesModified;
    }

    /**
     * Returns true if two plates have the same dimensions and registration.
     */
    private function platesMatchRegAndSize(array $plate1, array $plate2) : bool
    {
        return (
            $plate1['width'] === $plate2['width'] &&
            $plate1['height'] === $plate2['height'] &&
            $plate1['registration'] === $plate2['registration']
        );
    }

    /**
     * Finds differening design elements between two plates based on the
     * scrutinise attributes.
     */
    private function findElementsToScrutinise(array $plate1, array $plate2) : array
    {
        $differences = [];

        foreach ($this->toScrutinise as $key => $attribute) {
            $value1 = $this->getNestedValue($plate1, $attribute);
            $value2 = $this->getNestedValue($plate2, $attribute);

            if ($value1 !== $value2) {
                $differences[$key] = $value1;
            }
        }

        return $differences;
    }

    /**
     * Get a nested value from an array using a dot notation string.
     */
    private function getNestedValue(array $array, string $key) : mixed
    {
        $keys = explode('.', $key);
        foreach ($keys as $k) {
            if (isset($array[$k])) {
                $array = $array[$k];
            } else {
                return null;
            }
        }
        return $array;
    }
}
