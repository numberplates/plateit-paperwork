# Plateit Paperwork

A simple PHP application that renders paperwork html for printing when a JSON payload is posted to it consisting of an array of Plateit packages.

Note: Google Chrome (or Chromium) has been found to render the page styling more accurately than other browsers tested.

## Directory Structure

- `public/index.php`: web entry point; set the web server document root to `public/`.
- `src/`: PHP classes, `utils/`, bundled `templates/` and private `assets/` (including label fonts).
- `overrides/`: optional, Git-ignored shared and company template customisations.
- `vendor/`: Composer dependencies.

After updating an existing installation, run `composer install` to refresh the autoloader and update the web server document root to `public/`.

## Template Files and Directory Structure

The main HTML document is generated from `src/templates/outer/document.twig`. Local customisations live in an optional, Git-ignored `overrides/` directory, following the same conventions as the order portal and transactional emails apps.

Content template lookup order (first match wins):

1. `overrides/companies/<id>/`
2. `overrides/shared/`
3. `src/templates/inner/`

Copy only files that differ. For example, copy `src/templates/inner/header.twig` to `overrides/companies/123/header.twig` to change company 123's header, or to `overrides/shared/header.twig` to change it for all companies. Nested includes use the same lookup order, so overriding a header does not require copying `content.twig`. Use bare template names such as `header.twig` in overrides to retain fallback behaviour.

Each package resolves `content.twig` and its partials using its own `package.order.company_id`, including in mixed-company batches. Structural templates (`document.twig`, `base.css`, `package.twig` and `labels.twig`) always load from the bundled `src/templates/outer/` directory via the reserved `@outer` namespace. Files with these names under `overrides/` do not replace the application layout or labels. Use the content templates and `styles.css` for local branding. To reuse a bundled inner partial explicitly from an override, use e.g. `@inner/header.twig`; this avoids including the override itself.

Existing custom templates in the old `templates/company-<id>/` should be moved to `overrides/companies/<id>/`. Replace includes such as `/default/header.twig` with `header.twig` to allow individual partial overrides.

Create override directories as needed and back them up separately. They are not included in fresh checkouts; without them the application renders the bundled defaults.

## Custom Stylesheets

Optional plain CSS files load after the bundled `src/templates/outer/base.css`, in this order:

1. `overrides/shared/styles.css`
2. `overrides/companies/<id>/styles.css` for each company present in the batch (once per company, in first-appearance order).

Styles are embedded in the document head; no build or public asset URL is needed. CSS files are not processed as Twig.

Packing-slip content is wrapped in `.packing-slip[data-company-id="<id>"]`. Labels remain outside this wrapper. Prefix every company selector with its wrapper to avoid affecting other companies in mixed batches:

```css
.packing-slip[data-company-id="2"] .header {
    font-family: Georgia, serif;
    border-bottom: 1px solid #a87940;
}
```

Use `.packing-slip` for shared content styles. Keep page and label geometry in the outer `base.css` and `document.twig`: do not target `html`, `body`, `article`, `.labels`, `.label`, `.page-break` or `@page` from custom stylesheets. Avoid positioning content outside its wrapper and check that your content fits when printing. These are developer conventions; CSS is loaded as written, without automatic scoping or validation.

## Default Integrated Label Styles

The application natively supports some popular integrated label styles available from [Priory Direct]('https://www.priorydirect.co.uk/integrated-labels').

* [`priory-direct-single-up`](https://www.priorydirect.co.uk/integrated-labels/single-integrated-label-style-up-20654.html) (default)
* [`priory-direct-single-b`](https://www.priorydirect.co.uk/integrated-labels/single-integrated-label-style-b-13695.html)
* [`priory-direct-single-r`](https://www.priorydirect.co.uk/integrated-labels/single-integrated-labels-style-r-16615.html)
* [`priory-direct-double-d`](https://www.priorydirect.co.uk/integrated-labels/double-integrated-label-style-d-14643.html)

Another popular integrated label style that is natively supported (which is not available from Priory Direct) is a simple half-sheet whereby the label is exactly one half of the page.

* [`half-sheet`](https://uk.onlinelabels.com/products/eu30003)

Label styles are handled from inside the `IntegratedLabelType` class. More styles can be set using its constructor method (see the class's doc blocks for more information).

A specific label style can be loaded automatically by passing the style type as a query string. For example: `https://paperwork.plateit.co.uk/?type=priory-direct-double-d`.

## Company-Specific Scrutinisation

Scrutinisation compares plates with the same registration and dimensions, flagging differences in configured fields. Different companies can map their own design object structure without replacing a template.

The first existing configuration wins (mappings replace the complete list, rather than merge):

1. `overrides/companies/<id>/scrutinise.php`
2. `overrides/shared/scrutinise.php`
3. `src/config/scrutinise.php` (bundled default mapping).

For example, create `overrides/companies/123/scrutinise.php`:

```php
<?php
return [
    'border enabled' => 'design_object.decoration.hasBorder',
    'border colour' => 'design_object.decoration.borderColour',
];
```

Keys are staff-facing labels; values are dot-separated paths into each plate. Include the enabled/visible field when a design retains settings for disabled elements. Missing paths resolve to `null`; confirm paths against a real payload. Return `[]` to disable comparisons. These PHP files are trusted local configuration and are Git-ignored with the rest of `overrides/`.

Existing `get_scrutinised_plates(plates)` calls automatically use the company's configuration. An explicit mapping passed as the second Twig argument takes precedence for that call, including `[]` to disable comparisons. Every call uses a fresh instance so settings do not carry over between calls or companies.

## Usage

### For Production:

Run `composer install` from the application root and configure Apache or NGINX to serve `public/` as the document root. Application code, templates, fonts, dependencies and local overrides live outside the document root.

### For Development:

First, locally run:

`composer install`

And then run:

`php -S localhost:8080 -t public`

This will make the application accessible at `http://localhost:8080`.