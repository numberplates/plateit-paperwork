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
