#!/bin/bash

set -eux # https://explainshell.com/explain?cmd=set+-eux

vendor/bin/phpunit
vendor/bin/phpcs -sp --standard=PHPCompatibility --ignore="*.css,*.js,*lib/StripeObject.php,*lib/HttpClient/CurlClient.php,*lib/net/authorize/util/Log.php" --parallel=8 another-wordpress-classifieds-plugin premium-modules
