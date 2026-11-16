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

The main HTML document is generated from `src/templates/document.twig`. Local customisations live in an optional, Git-ignored `overrides/` directory, following the same conventions as the order portal and transactional emails apps.

Template lookup order (first match wins):

1. `overrides/companies/<id>/`
2. `overrides/shared/`
3. `src/templates/`
4. `src/templates/default/`

Copy only files that differ. For example, copy `src/templates/default/header.twig` to `overrides/companies/123/header.twig` to change company 123's header, or to `overrides/shared/header.twig` to change it for all companies. Nested includes use the same lookup order, so overriding a header does not require copying `content.twig`. Use bare template names such as `header.twig` in overrides to retain fallback behaviour.

Each package resolves `package.twig`, `labels.twig`, `content.twig` and their partials using its own `package.order.company_id`, including in mixed-company batches. The outer `document.twig` and `document.css.twig` are shared across the batch and can be overridden in `overrides/shared/`. Copy `document.css.twig` there to customise the print stylesheet; it remains a Twig template with access to `label_config`.

Existing custom templates in the old `templates/company-<id>/` should be moved to `overrides/companies/<id>/`. Replace includes such as `/default/header.twig` with `header.twig` to allow individual partial overrides.

Create override directories as needed and back them up separately. They are not included in fresh checkouts; without them the application renders the bundled defaults.

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

## Usage

### For Production:

Run `composer install` from the application root and configure Apache or NGINX to serve `public/` as the document root. Application code, templates, fonts, dependencies and local overrides live outside the document root.

### For Development:

First, locally run:

`composer install`

And then run:

`php -S localhost:8080 -t public`

This will make the application accessible at `http://localhost:8080`.