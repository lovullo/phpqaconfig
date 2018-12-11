phpqaconfig
==========

Configuration for QA and development tools specific to the coding standards at
LoVullo Associates. This package, combined with `lovullo/phpqatools` is meant
to be a base for new PHP projects.

Installation
==========

Simply require it in your project's `composer.json`

```json
    "require-dev": {
        "lovullo/phpqaconfig": "@stable"
    }
```

Or

```sh
$ composer require --dev lovullo/phpqaconfig
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
<phpunit verbose="true">
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

PHP Lint Configuration
---------

By default, the included `build.xml` runs `php -l` on all PHP files in the project
except for the `vendor/` directory in the root of your project. You can override
this setting using the `phplintignore` property:

```xml
<?xml version="1.0" encoding="utf-8"?>
<project name="project-name">
  <property name="phplintignore" value="components/**/*,vendor/**/*" />

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

PHP_CodeSniffer Configuration
----------

You can customize the files that are ignored in `phpcs` checks by specifying
a `phpcsignore` property:

```xml
<?xml version="1.0" encoding="utf-8"?>
<project name="project-name">
  <property name="phpcsignore" value="app/,bin/,vendor/,web/" />

  <import file="${basedir}/vendor/lovullo/phpqaconfig/build.xml" />
</project>
```

By default, only the `vendor` directory is excluded from checks.

You can change what PHP_CodeSniffer standard uses by overriding the
`phpcsstandard` property:

```xml
<?xml version="1.0" encoding="utf-8"?>
<project name="project-name">
  <property name="phpcsstandard" value="PSR1,PSR2" />

  <import file="${basedir}/vendor/lovullo/phpqaconfig/build.xml" />
</project>
```

PHPMD Configuration
----------

You can customize the files that are ignored in `phpmd` checks by specifying
a `phpmdignore` property:

```xml
<?xml version="1.0" encoding="utf-8"?>
<project name="project-name">
  <property name="phpmdignore" value="app,bin,vendor,web" />

  <import file="${basedir}/vendor/lovullo/phpqaconfig/build.xml" />
</project>
```

By default, only the `vendor` directory is excluded from checks.

PHPCPD Configuration
----------

You can customize the files that are ignored in `phpcpd` checks by specifying
a `phpcpdignore` property:

```xml
<?xml version="1.0" encoding="utf-8"?>
<project name="project-name">
  <property name="phpcpdignore" value="--exclude vendor --exclude app" />

  <import file="${basedir}/vendor/lovullo/phpqaconfig/build.xml" />
</project>
```

By default, only the `vendor` directory is excluded from checks.

phpDox Configuration
----------

```xml
<?xml version="1.0" encoding="utf-8" ?>
<phpdox xmlns="http://xml.phpdox.net/config" silent="true">
    <bootstrap />

    <project name="phpdox" source="${basedir}/src" workdir="${basedir}/build/phpdox/xml">
        <collector publiconly="false" backend="parser">
            <include mask="*.php" />
            <inheritance resolve="true" />
        </collector>

        <generator output="${basedir}/build/docs">
            <enrich base="${basedir}/build">
                <source type="build" />

                <source type="phploc">
                    <file name="logs/phploc.xml" />
                </source>

                <source type="git">
                    <git binary="git" />
                    <history enabled="true" limit="15" cache="${phpDox.project.workdir}/gitlog.xml" />
                </source>

                <source type="checkstyle">
                    <file name="logs/checkstyle.xml" />
                </source>

                <source type="phpunit">
                    <file name="logs/phpunit" />
                </source>

                <source type="pmd">
                    <file name="logs/pmd.xml" />
                </source>
            </enrich>

            <build engine="html" enabled="true" output="html">
                <template dir="${phpDox.home}/templates/html" />
                <file extension="html" />
            </build>
        </generator>
    </project>
</phpdox>
```

Disabling QA Checks
----------

If you do not want certain QA tools to run for a project, add one or more of
the following `disable` properties prior to including the base `build.xml` file:

```xml
<?xml version="1.0" encoding="utf-8"?>
<project name="project-name">
  <property name="disable-phpunit" value="true" />
  <property name="disable-pdepend" value="true" />
  <property name="disable-phpmd" value="true" />
  <property name="disable-phpcs" value="true" />
  <property name="disable-phpcpd" value="true" />

  <import file="${basedir}/vendor/lovullo/phpqaconfig/build.xml" />
</project>
```
