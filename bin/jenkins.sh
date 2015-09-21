#!/bin/sh

composer install
bin/phpunit
phpqa --analyzedDir ./ --buildDir ./var/CI --ignoredDirs=bin,vendor