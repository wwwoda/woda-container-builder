<?php

declare(strict_types=1);

namespace Woda\ContainerBuilder;

use InvalidArgumentException;
use Laminas\ConfigAggregator\ArrayProvider;
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;
use Laminas\Di\ConfigProvider;
use Laminas\ServiceManager\ServiceManager;
use Psr\Container\ContainerInterface;

use function is_dir;

/**
 * @phpstan-type Providers list<class-string|callable(): array<array-key, mixed>|PhpFileProvider>
 */
final class ContainerBuilder
{
    private ?ContainerInterface $container = null;
    /** @phpstan-var Providers */
    private array $providers;
    private ?string $configFolder = null;
    private ?string $cachedConfigFile = null;

    /**
     * @param Providers $providers Array of providers. These may be callables, or string values
     *                             representing classes that act as providers. If the latter, they must
     *                             be instantiable without constructor arguments.
     */
    public function __construct(?array $providers = null)
    {
        $this->providers = $providers === null ? [] : [
            ConfigProvider::class,
            ...$providers,
        ];
    }

    /**
     * @param string $configFolder Configuration folder; Files that end with .global.php or .local.php
     *                             are loaded into the configuration.
     */
    public function withConfigFolder(string $configFolder): self
    {
        if (!is_dir($configFolder)) {
            throw new InvalidArgumentException("$configFolder is not a directory");
        }
        $clone = clone $this;
        $clone->container = null;
        $clone->configFolder = $configFolder;
        $clone->providers[] = new PhpFileProvider($clone->configFolder . '/{{,*.}global,{,*.}local}.php');
        return $clone;
    }

    /**
     * @param string $cachedConfigFile Configuration cache file; config is loaded from this file if present,
     *                                 and written to it if not.
     */
    public function withCachedConfigFile(string $cachedConfigFile): self
    {
        $clone = clone $this;
        $clone->container = null;
        $clone->cachedConfigFile = $cachedConfigFile;
        $clone->providers[] = new ArrayProvider([ConfigAggregator::ENABLE_CACHE => true]);
        return $clone;
    }

    public function build(): ContainerInterface
    {
        if ($this->container !== null) {
            return $this->container;
        }

        $config = $this->buildConfig();
        $this->container = $this->buildContainer($config);
        return $this->container;
    }

    private function buildConfig(): ConfigAggregator
    {
        return new ConfigAggregator($this->providers, $this->cachedConfigFile);
    }

    private function buildContainer(ConfigAggregator $config): ContainerInterface
    {
        $config = $config->getMergedConfig();
        $dependencies = $config['dependencies'] ?? [];
        $dependencies['services']['config'] = $config;
        return new ServiceManager($dependencies);
    }
}
