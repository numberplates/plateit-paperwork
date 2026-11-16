<?php

namespace Paperwork;

/**
 * The IntegratedLabelType class manages a collection of label configurations,
 * allowing for the retrieval of specific label measurements. This data is
 * intended to be used to style the paperwork page/s accordingly.
 */
class IntegratedLabelType
{
    /**
     * Available presets, in fallback order.
     */
    private array $labelTypes;

    /**
     * The default label type key to use.
     */
    private string $defaultType;

    /**
     * Constructor optionally sets the label types if different from the
     * presets.
     */
    public function __construct(?array $labelTypes = null, ?string $defaultType = null)
    {
        $defaults = require __DIR__ . '/config/labels.php';
        $this->defaultType = $defaultType ?? ($labelTypes === null ? $defaults['default_type'] : ($labelTypes[0]['type'] ?? ''));
        $this->setLabelTypes($labelTypes ?? $defaults['types']);
        if (!$this->findLabelType($this->defaultType)) {
            throw new \InvalidArgumentException('Default label type must exist in the configured presets');
        }
    }

    /**
     * Sets the available label types if different from the presets.
     */
    public function setLabelTypes(array $labelTypes) : void
    {
        if ($labelTypes === [] || !array_is_list($labelTypes)) {
            throw new \InvalidArgumentException('Label types must be a non-empty list');
        }
        $seen = [];
        foreach ($labelTypes as $label) {
            if (!is_array($label)) {
                throw new \InvalidArgumentException('Each label preset must be an array');
            }
            foreach (['type', 'page_margin', 'label_width', 'label_height', 'label_margin'] as $field) {
                if (!isset($label[$field]) || !is_string($label[$field]) || trim($label[$field]) === '') {
                    throw new \InvalidArgumentException('Label presets require a non-empty ' . $field);
                }
            }
            $key = strtolower($label['type']);
            if (isset($seen[$key])) {
                throw new \InvalidArgumentException('Duplicate label type: ' . $label['type']);
            }
            $seen[$key] = true;
            if (isset($label['orientation']) && !in_array($label['orientation'], ['portrait', 'landscape'], true)) {
                throw new \InvalidArgumentException('Label orientation must be portrait or landscape');
            }
            if (isset($label['labels_per_page']) && (!is_int($label['labels_per_page']) || $label['labels_per_page'] < 1)) {
                throw new \InvalidArgumentException('Labels per page must be a positive integer');
            }
        }
        $this->labelTypes = $labelTypes;
    }

    /** Returns the requested preset, or the configured default if unknown. */
    public function getLabelType(string|null $type = null) : array
    {
        return ($type !== null ? $this->findLabelType($type) : null)
            ?? $this->findLabelType($this->defaultType)
            ?? $this->labelTypes[0];
    }

    private function findLabelType(string $type): ?array
    {
        foreach ($this->labelTypes as $label) {
            if (strcasecmp($label['type'], $type) === 0) {
                return $label;
            }
        }
        return null;
    }

    /** Loads optional installation settings without modifying bundled defaults. */
    public static function fromRoot(string $root): self
    {
        $config = require $root . '/src/config/labels.php';
        $path = $root . '/overrides/shared/labels.php';
        if (is_file($path)) {
            $overrides = (static function (string $file) { return require $file; })($path);
            if (!is_array($overrides)) {
                throw new \InvalidArgumentException('Label configuration must return an array');
            }
            $config = array_replace($config, $overrides);
        }
        if (!is_array($config['types'] ?? null) || !is_string($config['default_type'] ?? null)) {
            throw new \InvalidArgumentException('Label configuration requires types and default_type');
        }
        return new self($config['types'], $config['default_type']);
    }
}
