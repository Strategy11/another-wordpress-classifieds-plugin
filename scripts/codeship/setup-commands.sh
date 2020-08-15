#!/bin/bash

set -eux # https://explainshell.com/explain?cmd=set+-eux

phpenv local 7.2

composer config -g github-oauth.github.com $GITHUB_ACCESS_TOKEN

composer install --prefer-dist --no-interaction
