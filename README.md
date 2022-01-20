# woda/container-builder

_Create a PSR-11 Dependency Injection container with laminas-servicemanager and laminas-di_

---

## Table of Contents

- [What it's about (technical explanation)](#what-its-about-technical-explanation)
- [Why you should use it?](#why-you-should-use-it)
- [Usage (in a WordPress theme)](#usage-in-a-wordpress-theme)
  - [Installation](#installation)
  - [Setup](#setup)
  - [Config Providers](#config-providers)

## What it's about (technical explanation)

This package allows you to quickly set up a dependency injection (DI) container and service providers within your project. No more, no less.

It creates a [PSR-11](http://www.php-fig.org/psr/psr-11/) container, configured to use [laminas-servicemanager](https://docs.laminas.dev/laminas-servicemanager) and [laminas-di](https://docs.laminas.dev/laminas-di).

The typical use case this package was intended for is when building a WordPress theme or plugin where you want to glue together "packages" using [Composer](https://getcomposer.org/).

It's not limited to WordPress though. That's just what we use it for. You can technically utilize it wherever you want.

It's a very bare-bone implementation to solve one goal only: Setting up a DI container quickly and easily.

## Why you should use it?

While it may be daunting or seem like overhead at a first glance to use a DI container, it has many advantages. Once you clear the first hurdle of learning the basics, you will never want to go back doing things the *old* way.

To counter your first concern: **No, this is not a framework.** This package is simply a ready-to-use implementation to get you going with writing clean and awesome quality-code quickly. What you make of it is up to you.

But let's get back to the advantages of using a DI container:

1. Firstly, you'll become a better developer. 🧐
2. You will create cleaner and more readable code. 📖
3. You will quickly learn to become much more productive by creating reusable packages instead of writing the same feature over and over again. 🚀
4. You're working on a large project? It's going to be much easier to maintain it. 🗺️
5. Your code will be more flexible because it is only loosely coupled. 💪
6. Your code will be more testable. 🧪

## Usage (in a WordPress theme)

We'll explain the usage of this package inside a WordPress theme.

Typically `woda/container-builder` is used in combination with packages that provide [**ConfigProviders**](#config-providers). [**ConfigProviders**](#config-providers) are simple callables without dependencies, that return a config array.

Two packages that we use in each and every WordPress project are

* [woda/wp-hook](https://github.com/wwwoda/woda-wp-hook) - Helps you to quickly register packages that will automatically set themselves up by hooking into WordPress actions and filters.
* [woda/wp-config](https://github.com/wwwoda/woda-wp-config) - Provides type-safe access to array configurations.

so we'll illustrate the setup with these two in mind.

### Installation


```bash
composer require woda/container-builder woda/wp-config woda/wp-hook
```

Omit `woda/wp-config woda/wp-hook` if you only want to require this package.

### Setup

Add this to the top of your theme's `functions.php` file:

```php
<?php

declare(strict_types=1);

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    throw new RuntimeException('Autoloader is not installed. Please run "composer install".');
}
require __DIR__ . '/vendor/autoload.php';

$container = (new \Woda\ContainerBuilder\ContainerBuilder(require __DIR__ . '/resources/config-providers.php'))
    ->withConfigFolder(__DIR__ . '/resources/config')
    ->withCachedConfigFile(__DIR__ . '/resources/cached-config.php')
    ->build();
// Call the HookCallbackProviderInterface to register hooks
$container->get(\Woda\WordPress\Hook\HookCallbackProviderInterface::class)->registerCallbacks();
```

First, we'll define the path to a file `resources/config-providers.php` that returns an array of [ConfigProviders](#config-providers) that we wish to load. More on that later.

Then we define the folder that will contain our configuration files with `withConfigFolder()`. All files inside this folder ending with `.global.php` will be loaded first and files ending with `.local.php` will be loaded last. This allows you to override 
settings in the global config with local settings (which you should obviously add to `.gitignore`).

Afterwards, we define a file path to a configuration cache file with `withCachedConfigFile()` that will be generated on the fly and used for subsequent requests.

In the last step, we build our container with `build()`.

At the end of our example implementation above we additionally initialize the [woda/wp-hook](https://github.com/wwwoda/woda-wp-hook) package. All packages that want to hook into WordPress actions and filters will do so at this point.

### Config Providers

So what's actually a ConfigProvider?

In the most basic sense, they return a configuration array.

Often they will contain information about dependencies.

ConfigProviders are for example the place where you instruct your application which factory should create a Class that gets injected into another Class.

They also contain aliases so your application knows which implementation of an Interface to inject into a Class that depends on it.

You can read more on that by checking out the documentation for the [laminas-servicemanager](https://docs.laminas.dev/laminas-servicemanager/configuring-the-service-manager/).

In our case we want to load our two packages' ConfigProviders so we'll add those to `resources/config-providers.php`:

```php
<?php

return [
    \Woda\WordPress\Config\ConfigProvider::class,
    \Woda\WordPress\Hook\ConfigProvider::class,
];
```
