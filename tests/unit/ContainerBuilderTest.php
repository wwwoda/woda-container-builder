<?php

declare(strict_types=1);

namespace Woda\Test\Unit\ContainerBuilder;

use InvalidArgumentException;
use Laminas\ConfigAggregator\ConfigAggregator;
use PHPUnit\Framework\TestCase;
use Woda\ContainerBuilder\ContainerBuilder;

class ContainerBuilderTest extends TestCase
{
    public function testContainerConfigKeyIsSet(): void
    {
        $builder = new ContainerBuilder([]);

        self::assertIsArray($builder->build()->get('config'));
    }

    public function testWithCachedConfigFileEnablesCache(): void
    {
        $builder = (new ContainerBuilder([]))->withCachedConfigFile('/cache/config.php');

        $config = $builder->build()->get('config');

        self::assertIsArray($config);
        self::assertArrayHasKey(ConfigAggregator::ENABLE_CACHE, $config);
        self::assertTrue($config[ConfigAggregator::ENABLE_CACHE]);
    }

    public function testThrowsExceptionWhenConfigFolderDoesNotExist(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ContainerBuilder([]))->withConfigFolder('/does/not/exist')->build();
    }
}
