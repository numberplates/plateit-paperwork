<?php
// Import dependencies.
require_once 'vendor/autoload.php';

// Set default values for environment variables if they are not already set.
putenv('PLATEIT_BASE_URL=' . ($_ENV['PLATEIT_BASE_URL'] ?? 'https://data.plateit.co.uk/v3'));

// Initialise the Twig template engine.
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/templates');
$twig = new \Twig\Environment($loader);

// Register custom Twig filters.
$twig->addFilter(new \Twig\TwigFilter('format_price', 'penceToPounds'));
$twig->addFilter(new \Twig\TwigFilter('mm_to_inches', 'mmToReadableInches'));
$twig->addFilter(new \Twig\TwigFilter('strip_slashes', 'stripslashes'));

// Convert the incoming JSON to a PHP array.
$payload = json_decode(file_get_contents('php://input'), true);

// Ensure payload exists.
if(empty($payload)) {
   kill('Missing JSON payload');
}

// Render the html for printing.
echo $twig->render('packing-slip.twig', [
   'packages' => $payload,
   'env' => $_ENV
]);
