# linotype

Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
$ composer require linotype/symfony
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require linotype/symfony
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Linotype\Bundle\LinotypeBundle\LinotypeBundle::class => ['all' => true],
];
```

```yaml
// config/routes/linotype.yaml

linotype:
  resource: '@LinotypeBundle/Resources/config/routes.yaml'
```

```yaml
// config/packages/twig.yaml

twig:
    default_path: '%kernel.project_dir%/linotype'
```