# Plateit Paperwork

A simple PHP application that renders paperwork html for printing when a JSON payload is posted to it consisting of an array of Plateit packages.

Note: Google Chrome (or Chromium) has been found to render the page styling more accurately than other browsers tested.

## Template Files and Directory Structure

The main HTML document is generated from the `/templates/document.twig` file, which serves as the entry point.

This outer document template will attempt to import a company-specific content template for each package, located at `/templates/company-{id}/content.twig` (the directory's `{id}` placeholder is replaced with the actual company ID).

If a company-specific template directory does not exist with a containing `content.twig` template, the application will fall back to the default packing slip template found at `/templates/default/content.twig`.

## Default Integrated Label Styles

The application natively supports some popular integrated label styles available from [Priory Direct]('https://www.priorydirect.co.uk/integrated-labels').

* Style `DD` (default)
* Style `B`
* Style `UP`

This is handled from inside the `IntegratedLabelType` class. More styles can be set using its constructor method (see the class's doc blocks for more information).

A specific label style can be loaded automatically by passing the style type as a query string. For example: `https://paperwork.plateit.co.uk/?type=UP`.

## Usage

The instructions below assume you are using Docker or Podman. If not, when moved to an Apache or NGINX hosting environment, it should *just work* (providing the dependencies have been installed using Composer).

### For Production:

`podman compose up -d`

Then access the app at `http://your.server:8080`

### For Development:

First, locally run:

`composer install`

And then either run:

* `php -S localhost:8080`, or
* `podman compose -f docker-compose.yaml -f docker-compose.dev.yaml up -d`

The first command will run a local PHP development server (quickest method).

The second command will build and run a Docker/Podman container with an editable volume.

Both commands will make the application accessible at `http://localhost:8080`.