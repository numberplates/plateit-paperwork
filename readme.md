# Plateit Paperwork

A simple PHP application that renders paperwork html for printing when a JSON payload is posted to it consisting of an array of Plateit packages.

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