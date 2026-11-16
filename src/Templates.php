<?php
namespace Paperwork;

/** Resolves each package's partials independently, including nested includes. */
final class Templates
{
    private array $environments = [];

    public function __construct(private string $root) {}

    public function render(array $context): string
    {
        // Load plain CSS once per company, after shared styles.
        $paths = [$this->root . '/overrides/shared/styles.css'];
        foreach ($context['packages'] ?? [] as $package) {
            $id = $package['order']['company_id'] ?? null;
            if ((is_int($id) || is_string($id)) && preg_match('/^[0-9]+$/D', (string) $id)) {
                $paths[] = $this->root . '/overrides/companies/' . $id . '/styles.css';
            }
        }
        $context['custom_styles'] = '';
        foreach (array_unique($paths) as $path) {
            if (is_file($path)) {
                $css = file_get_contents($path);
                if ($css === false) {
                    throw new \RuntimeException('Unable to read stylesheet: ' . $path);
                }
                $context['custom_styles'] .= "\n" . $css;
            }
        }
        return $this->environment()->render('@outer/document.twig', $context);
    }

    /** First configured mapping wins; an empty mapping disables comparison. */
    private function scrutiniseFields(?string $companyId): array
    {
        $paths = [
            $companyId !== null ? $this->root . '/overrides/companies/' . $companyId . '/scrutinise.php' : '',
            $this->root . '/overrides/shared/scrutinise.php',
            $this->root . '/src/config/scrutinise.php',
        ];
        foreach ($paths as $path) {
            if (!is_file($path)) {
                continue;
            }
            $fields = (static function (string $file) { return require $file; })($path);
            if (!is_array($fields)) {
                throw new \RuntimeException('Scrutinise configuration must return an array: ' . $path);
            }
            foreach ($fields as $label => $field) {
                if (!is_string($label) || trim($label) === '' || !is_string($field) || trim($field) === '') {
                    throw new \RuntimeException('Scrutinise configuration must map non-empty labels to field paths: ' . $path);
                }
            }
            return $fields;
        }
        throw new \RuntimeException('Missing bundled scrutinise configuration');
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
            $this->root . '/src/templates/inner',
        ], 'is_dir'));
        // Initialise the Twig template engine.
        $loader = new \Twig\Loader\FilesystemLoader($paths);
        // Structural templates always come from the bundled application namespace.
        $loader->addPath($this->root . '/src/templates/outer', 'outer');
        $loader->addPath($this->root . '/src/templates/inner', 'inner');
        $twig = new \Twig\Environment($loader);

        $scrutiniseFields = $this->scrutiniseFields($companyId);

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
        $twig->addFunction(new \Twig\TwigFunction('get_scrutinised_plates', function ($plates, $toScrutinise = null) use ($scrutiniseFields) {
           return (new Scrutinise($plates, $toScrutinise ?? $scrutiniseFields))->getScrutinisedPlates();
        }));

        // Register custom Twig function to generate a manual-post shipping label.
        $twig->addFunction(new \Twig\TwigFunction('get_fallback_label_image', function ($package) use ($manualLabel) {
           $shipToAddress = getCorrectShipToAddress($package);
           return $manualLabel
              ->setShipToAddress($shipToAddress)
              ->setBarcodeData($package['id'] ?? '')
              ->generateLabel();
        }));

        $twig->addFunction(new \Twig\TwigFunction('render_content', function (array $context) {
            $id = $context['package']['order']['company_id'] ?? null;
            $companyId = is_int($id) || is_string($id) ? (string) $id : null;
            return $this->environment($companyId)->render('content.twig', $context);
        }, ['needs_context' => true, 'is_safe' => ['html']]));
        return $this->environments[$key] = $twig;
    }
}
