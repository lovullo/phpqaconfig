phpqaconfig
==========

Configuration for QA and development tools specific to the coding standards at
LoVullo Associates. This package, combined with `lovullo/phpqatools` is meant
to be a base for new PHP projects.

Installation
==========

Simply require it in your project's `composer.json`

```json
    "require": {
        "lovullo/phpqaconfig": "@stable"
    }
```

Or

```sh
$ composer require lovullo/phpqaconfig
```

Usage
==========

Create a `build.xml` file in your project root with the following contents:

```xml
<?xml version="1.0" encoding="utf-8"?>
<project name="project-name">
  <import file="${basedir}/vendor/lovullo/phpqaconfig/build.xml" />
</project>
```

Create a `phpunit.xml` file in your project root with the following contents:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="tests/bootstrap.php"
         backupGlobals="false"
         backupStaticAttributes="false"
         strict="true"
         verbose="true">

  <testsuites>
    <testsuite name="ProjectName">
      <directory suffix="Test.php">tests/</directory>
    </testsuite>
  </testsuites>

  <logging>
    <log type="coverage-html" target="build/coverage"/>
    <log type="coverage-clover" target="build/logs/clover.xml"/>
    <log type="coverage-crap4j" target="build/logs/crap4j.xml"/>
    <log type="junit" target="build/logs/junit.xml" logIncompleteSkipped="false"/>
  </logging>
</phpunit>

```

When developing, use the following ant targets:

* composer
* phpunit
* phpcs
* phpmd
* phpcpd
* phploc

For example:

```sh
$ ant phpcs phpmd phpcpd
```

When doing Continuous Integration (ie. Jenkins), make sure the build process is
configured to run the following steps in order:

```
check_mergeconflicts
phplint
composer-ci
phpunit
phpcs-ci
phpmd-ci
phpcpd-ci
phploc-ci
phpdox
```

Add additional build targets to your `build.xml` file as appropriate for your
particular project.

