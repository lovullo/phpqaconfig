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

Project Setup
----------

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

See the "Customizing" section below if you have an existing PHPUnit
configuration elsewhere.

Development
----------

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

Continuous Integration
-----------

When configuring Continuous Integration (ie. Jenkins), `composer` must download
and install this repo before you can run `ant` tasks. Add an "Execute Shell"
build step to Jenkins with the following command:

```sh
$ composer install -o
```

Next, add an "Invoke Ant" build step with the following target:

```
build-qa
```

This will run `check_mergeconflicts`, `phplint`, `phpunit`, `phpcs-ci`,
`pdepend`, `phpmd-ci`, `phpcpd-ci`, `phploc-ci`, and `phpdox`.

For jobs that need to run quickly and only need a minimal number of QA checks,
run the following targets instead:

```
build-quick
```

This will run `check_mergeconflicts`, `phplint`, and `phpunit`.

Add additional build targets to your `build.xml` file as appropriate for your
particular project.

Customizing
==========

QA Tools Location
----------

The included `build.xml` assumes that all the QA tools are installed into
`./vendor/bin`. If they are installed elsewhere, provide a `toolsdir` property
to their location:

```xml
<?xml version="1.0" encoding="utf-8"?>
<project name="project-name">
  <property name="toolsdir" value="${basedir}/bin/" />

  <import file="${basedir}/vendor/lovullo/phpqaconfig/build.xml" />
</project>
```

PHPUnit Configuration
----------

By default, the included `build.xml` assumes that there is a `phpunit.xml` file
in the same directory as itself. If you already have a `phpunit.xml` file
located somewhere else, you can override the configuration as follows:

```xml
<?xml version="1.0" encoding="utf-8"?>
<project name="project-name">
  <property name="phpunitconfig" value="${basedir}/app/" />

  <import file="${basedir}/vendor/lovullo/phpqaconfig/build.xml" />
</project>
```

