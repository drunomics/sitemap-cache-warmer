# drunomics Sitemap CacheWarmer

A simple cache warmer, that reads the sitemap.xml file(s) and warms all URLs.

The cache warmer is intended for CLI usage and outputs useful timing and
HTTP status code information.

## Installation

     git clone git@github.com:drunomics/sitemap-cache-warmer.git
     cd sitemap-cache-warmer
     composer install

## Usage

Usage may vary based upon your configuration. With the default configuration
the following usage is supported:

    php run.php https://drunomics.com

Alternatively the URL may be provided via the `PHAPP_BASE_URL` environment 
variable.

### HTTP authentication

Optionally, HTTP authentication is provided based upon the environment variables
`HTTP_AUTH_USER` and `HTTP_USER_PASSWORD`.

Example:

    HTTP_AUTH_USER=user HTTP_AUTH_PASSWORD='password' php run.php https://example.com

## Configuration

All Guzzle requestion options can be controlled from config.
Just copy config.inc.php.dist to config.inc.php and changed its content.
