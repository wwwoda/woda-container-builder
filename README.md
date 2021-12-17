# woda/container-builder

Create a PSR-11 container with laminas-servicemanager with laminas-di

## Install

```bash
composer require woda/container-builder
```

## Description

The ContainerBuilder creates a [PSR-11](http://www.php-fig.org/psr/psr-11/) container, configured to use 
[laminas-servicemanager](https://docs.laminas.dev/laminas-servicemanager) and 
[laminas-di](https://docs.laminas.dev/laminas-di).

## Usage

```php
<?php

declare(strict_types=1);

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    throw new RuntimeException('Autoloader is not installed. Please run "composer install".');
}
require __DIR__ . '/vendor/autoload.php';

$container = (new \Woda\ContainerBuilder\ContainerBuilder([/* ... */]))
    // Load files in folder with pattern {{,*.}global,{,*.}local}.php
    ->withConfigFolder(__DIR__ . '/resources/config')
    // Cache merged configuration in resources/cached-config.php
    ->withCachedConfigFile(__DIR__ . '/resources/cached-config.php')
    ->build();

// ... work with $container
```

### Usage inside a WordPress Theme

`woda/container-builder` is primarily used in combination with other packages that provide ConfigProviders.
ConfigProviders are simple callables, without dependencies, that return a config array. In this example we use two 
additional packages:

* [woda/wp-config](https://github.com/wwwoda/woda-wp-config)
* [woda/wp-hook](https://github.com/wwwoda/woda-wp-hook)

```bash
composer require woda/wp-config woda/wp-hook
```

The ContainerBuilder allows to set a folder for configuration files. In this config folder, all files that end with 
`.global.php` will be loaded first and files ending with `.local.php` will be loaded last. This allows you to override 
settings in the global config with local settings. (`.local.php` files should be ignored by git.)

In the example below the `resources/config` folder exist inside the themes root and is used for project specific 
configurations. An array of ConfigProviders is listed inside a separate file `resources/config-providers.php`. The file
`resources/cached-config.php` will be generated on the fly and used for subsequent requests.

The themes `functions.php` file would look like this:

```php
<?php

declare(strict_types=1);

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    throw new RuntimeException('Autoloader is not installed. Please run "composer install".');
}
require __DIR__ . '/vendor/autoload.php';

$container = (new \Woda\Laminas\ContainerBuilder\ContainerBuilder(require __DIR__ . '/resources/config-providers.php'))
    ->withConfigFolder(__DIR__ . '/resources/config')
    ->withCachedConfigFile(__DIR__ . '/resources/cached-config.php')
    ->build();
// Call the HookCallbackProviderInterface to register hooks
$container->get(\Woda\WordPress\Hook\HookCallbackProviderInterface::class)->registerCallbacks();
```

`resources/config-providers.php`:

```php
<?php

return [
    \Woda\WordPress\Config\ConfigProvider::class,
    \Woda\WordPress\Hook\ConfigProvider::class,
];
```
