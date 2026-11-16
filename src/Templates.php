<?php
namespace Paperwork;

/** Resolves each package's partials independently, including nested includes. */
final class Templates
{
    private array $environments = [];

    public function __construct(private string $root) {}

    public function render(array $context): string
    {
        return $this->environment()->render('document.twig', $context);
    }

    private function environment(?string $companyId = null): \Twig\Environment
    {
        // Company IDs must be a single numeric directory name.
        $companyId = $companyId !== null && preg_match('/^[0-9]+$/D', $companyId)
            ? $companyId : null;
        $key = $companyId ?? 'shared';
        if (isset($this->environments[$key])) {
            return $this->environments[$key];
        }
        $paths = array_values(array_filter([
            $companyId !== null ? $this->root . '/overrides/companies/' . $companyId : '',
            $this->root . '/overrides/shared',
            $this->root . '/src/templates',
            $this->root . '/src/templates/default',
        ], 'is_dir'));
        // Initialise the Twig template engine.
        $loader = new \Twig\Loader\FilesystemLoader($paths);
        $twig = new \Twig\Environment($loader);

        // Initialise the Scrutinise class.
        $scrutinise = new \Paperwork\Scrutinise();

        // Initialise the ManualShippingLabel class.
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

        // Register custom Twig function to return the correct shipping address.
        $twig->addFunction(new \Twig\TwigFunction('get_correct_ship_to_address', function ($packageWithOrder) {
           return getCorrectShipToAddress($packageWithOrder);
        }));

        // Register custom Twig function to find subtle plate design differences.
        $twig->addFunction(new \Twig\TwigFunction('get_scrutinised_plates', function ($plates, $toScrutinise = null) use ($scrutinise) {
           $scrutinise->setPlates($plates);
           if($toScrutinise){ $scrutinise->setScrutinise($toScrutinise); }
           return $scrutinise->getScrutinisedPlates();
        }));

        // Register custom Twig function to generate a manual-post shipping label.
        $twig->addFunction(new \Twig\TwigFunction('get_fallback_label_image', function ($package) use ($manualLabel) {
           $shipToAddress = getCorrectShipToAddress($package);
           return $manualLabel
              ->setShipToAddress($shipToAddress)
              ->setBarcodeData($package['id'] ?? '')
              ->generateLabel();
        }));

        $twig->addFunction(new \Twig\TwigFunction('render_package', function (array $context) {
            $id = $context['package']['order']['company_id'] ?? null;
            $companyId = is_int($id) || is_string($id) ? (string) $id : null;
            return $this->environment($companyId)->render('package.twig', $context);
        }, ['needs_context' => true, 'is_safe' => ['html']]));
        return $this->environments[$key] = $twig;
    }
}
