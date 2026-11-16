<?php
// Import dependencies.
$root = dirname(__DIR__);
require_once $root . '/vendor/autoload.php';

// Convert the incoming JSON to a PHP array.
$packages = json_decode(file_get_contents('php://input'), true);

// Ensure payload exists.
if(empty($packages)) {
   kill('Missing JSON payload');
}

$templates = new \Paperwork\Templates($root);
$integratedLabelType = \Paperwork\IntegratedLabelType::fromRoot($root);

// Render the html for printing.
echo $templates->render([
    'label_config' => $integratedLabelType->getLabelType($_GET['type'] ?? null),
    'batch_code' => generateRandomCode(3),
    'packages' => $packages,
    'plateit_base_url' => resolvePlateitBaseUrl(),
]);