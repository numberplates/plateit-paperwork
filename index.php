<?php
// Import dependencies.
require_once 'vendor/autoload.php';

// Set default values for environment variables if they are not already set.
putenv('PLATEIT_BASE_URL=' . ($_ENV['PLATEIT_BASE_URL'] ?? 'https://data.plateit.co.uk/v3'));

// Convert the incoming JSON to a PHP array.
$packages = json_decode(file_get_contents('php://input'), true);

// Ensure payload exists.
if(empty($packages)) {
   kill('Missing JSON payload');
}

// Initialise the Twig template engine.
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/templates');
$twig = new \Twig\Environment($loader);

// Initialise the Scrutinise class.
$scrutinise = new \Paperwork\Scrutinise();

// Initialise the Scrutinise class.
$manualLabel = new \Paperwork\ManualShippingLabel();

// Register custom Twig filters.
$twig->addFilter(new \Twig\TwigFilter('format_price', 'penceToPounds'));
$twig->addFilter(new \Twig\TwigFilter('mm_to_inches', 'mmToReadableInches'));
$twig->addFilter(new \Twig\TwigFilter('strip_slashes', 'stripslashes'));

// Register custom Twig function to check if a given template file path exists.
$twig->addFunction(new \Twig\TwigFunction('template_exists', function ($templatePath) use ($twig) {
   return $twig->getLoader()->exists($templatePath);
}));

// Register custom Twig function to render a html list from an assoc array.
$twig->addFunction(new \Twig\TwigFunction('render_array_list', function ($array) {
   return renderArrayAsHtmlList($array ?? []);
}));

// Register custom Twig function to find subtle plate design differences.
$twig->addFunction(new \Twig\TwigFunction('get_scrutinised_plates', function ($plates, $toScrutinise = null) use ($scrutinise) {
   $scrutinise->setPlates($plates);
   if($toScrutinise){ $scrutinise->setScrutinise($toScrutinise); }
   return $scrutinise->getScrutinisedPlates();
}));

// Register custom Twig function to generata manual-post shipping label.
$twig->addFunction(new \Twig\TwigFunction('get_fallback_label_image', function ($package) use ($manualLabel) {
   $manualLabel->setPackage($package);
   return $manualLabel->generateLabel();
}));

// Render the html for printing.
echo $twig->render('document.twig', [
   'packages' => $packages,
   'env' => $_ENV
]);