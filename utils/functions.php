<?php

/**
 * Terminates the script with a custom error message and HTTP status code.
 */
function kill(string $message, int $statusCode = 400) : void
{
    http_response_code($statusCode);
    die($message);
}

/**
 * Converts a given amount in pence to pounds with high precision.
 * 
 * This function uses BCMath to perform the division if available, ensuring
 * precise results. If BCMath is not available, it falls back to using standard
 * floating point arithmetic with rounding. It intentionally returns a string
 * instead of a floating point to ensure a precision-correct conversion.
 */
function penceToPounds(int $pence) : string
{
    if (function_exists('bcdiv')) {
        return bcdiv($pence, '100', 2);
    } else {
        return number_format(round($pence / 100, 2), 2, '.', '');
    }
}

/**
 * Converts mm to readable inches (without any crazy decimals). It rounds up or
 * down to the nearest 0.1.
 */
function mmToReadableInches($mm) : string
{
    $inchesPerMillimeter = 0.0393701;
    $inches = $mm * $inchesPerMillimeter;
    $rounded = round($inches * 10) / 10;

    // Check if rounded value is an integer.
    if (floor($rounded) == $rounded) {
        return strval($rounded);
    }

    $roundedString = number_format($rounded, 1, '.', '');
    return rtrim($roundedString, '.0');
}

/**
 * Takes an associative array and recursively generates an HTML structure where
 * each key-value pair is represented as a list item. If a value is an array, it
 * will be rendered as a nested list.
 */
function renderArrayAsHtmlList(array $array) : string
{
    $output = '<ul>';
    foreach ($array as $key => $value) {
        $output .= '<li>' . htmlspecialchars($key) . ': ';
        if (is_array($value) && isAssociativeArray($value)) {
            $output .= renderArrayAsHtmlList($value);
        }
        else if (is_array($value)) {
            $output .= json_encode($value);
        }
        else {
            $output .= htmlspecialchars($value ?? 'N/A');
        }
        $output .= '</li>';
    }
    $output .= '</ul>';
    return $output;
}

/**
 * Returns true if the given array is an associative array.
 */
function isAssociativeArray(array $array): bool
{
    if (empty($array)) {
        return false;
    }
    foreach (array_keys($array) as $key) {
        if (is_string($key)) {
            return true;
        }
    }
    return false;
}

/**
 * The shipping address is typically part of the order object. However, it can
 * be overridden on the package level. This function returns the package-level
 * address if present, otherwise it falls back to the order level address.
 */
function getCorrectShipToAddress(array $packageWithOrder) : array
{
    return $packageWithOrder['ship_to_override'] ?? $packageWithOrder['order']['ship_to'] ?? [];
}