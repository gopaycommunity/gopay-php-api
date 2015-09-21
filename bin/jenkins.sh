#!/bin/sh

composer install
phpqa --verbose --analyzedDir ./ --buildDir ./var/CI --ignoredDirs=bin,vendor
bin/phpunit --log-junit ./var/CI/junit.xml --testdox-text ./var/CI/testdox.txt --testdox-html ./var/CI/testdox.html