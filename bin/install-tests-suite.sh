#!/usr/bin/env bash

TESTS_DIR=$1
WORDPRESS_VERSION=$2
DB_NAME=$3
DB_USER=$4
DB_PASS=$5
DB_HOST=$6

# http serves a single offer, whereas https serves multiple. we only want one
if [[ "$WORDPRESS_VERSION" = "latest" ]]; then
    wget -nv -O /tmp/wp-latest.json http://api.wordpress.org/core/version-check/1.7/
    grep '[0-9]+\.[0-9]+(\.[0-9]+)?' /tmp/wp-latest.json
    WORDPRESS_VERSION=$(grep -o '"version":"[^"]*' /tmp/wp-latest.json | sed 's/"version":"//')

    if [[ -z "$WORDPRESS_VERSION" ]]; then
        echo "Latest WordPress version could not be found"
        exit 1
    fi
fi

WP_TESTS_TAG="tags/$WORDPRESS_VERSION"

# set up testing suite
mkdir -p $TESTS_DIR
cd $TESTS_DIR
svn co --quiet http://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/{data,includes}/

CONFIG_FILE="$TESTS_DIR/wp-tests-config.php"
wget -nv -O $CONFIG_FILE http://develop.svn.wordpress.org/${WP_TESTS_TAG}/wp-tests-config-sample.php

# portable in-place argument for both GNU sed and Mac OSX sed
if [[ $(uname -s) == 'Darwin' ]]; then
    SED='sed -i .bak'
else
    SED='sed -i'
fi

$SED "s/youremptytestdbnamehere/$DB_NAME/" $CONFIG_FILE
$SED "s/yourusernamehere/$DB_USER/" $CONFIG_FILE
$SED "s/yourpasswordhere/$DB_PASS/" $CONFIG_FILE
$SED "s|localhost|${DB_HOST}|" $CONFIG_FILE

cd $OLDPWD
