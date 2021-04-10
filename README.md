# linotype

Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Applications that use Symfony Flex
----------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require linotype/symfony
```

### Step 2: Install a linotype project starter

Create a [linotype repository project](https://docs.linotype.dev) or use the official starter

```console
$ composer require linotype/starter
```

### Step 3: Edit config files

Create route config file to enable linotype routes system

```yaml
// config/routes/linotype.yaml

linotype:
  resource: '@LinotypeBundle/Resources/config/routes.yaml'
```

Change the default twig path to linotype project directory

```yaml
// config/packages/twig.yaml

twig:
    default_path: '%kernel.project_dir%/linotype'
```
