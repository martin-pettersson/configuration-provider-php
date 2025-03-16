<?php

/*
 * Copyright (c) 2025 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N7e;

use N7e\DependencyInjection\ContainerBuilderInterface;
use N7e\DependencyInjection\ContainerInterface;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConfigurationProvider::class)]
final class ConfigurationProviderTest extends TestCase
{
    private ConfigurationProvider $provider;
    private MockObject $containerBuilderMock;
    private MockObject $containerMock;
    private MockObject $rootDirectoryAggregateMock;

    #[Before]
    public function setUp(): void
    {
        $this->containerBuilderMock = $this->getMockBuilder(ContainerBuilderInterface::class)->getMock();
        $this->containerMock = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $this->rootDirectoryAggregateMock = $this->getMockBuilder(RootDirectoryAggregateInterface::class)->getMock();
        $this->provider = new ConfigurationProvider();

        $this->containerBuilderMock->method('build')
            ->willReturn($this->containerMock);
        $this->containerMock->method('get')
            ->with(RootDirectoryAggregateInterface::class)
            ->willReturn($this->rootDirectoryAggregateMock);
        $this->rootDirectoryAggregateMock->method('getRootDirectory')
            ->willReturn(dirname(__FILE__) . '/fixtures');
    }

    private function capture(&$destination): Callback
    {
        return $this->callback(static function ($source) use (&$destination) {
            $destination = $source;

            return true;
        });
    }

    #[Test]
    public function shouldProvideManagedConfigurationObject(): void
    {
        $this->containerBuilderMock
            ->expects($this->exactly(2))
            ->method('addFactory')
            ->with($this->anything(), $this->capture($configurationFactory));

        $this->provider->configure($this->containerBuilderMock);
        $this->provider->load($this->containerMock);

        $this->assertEquals(
            [
                'configurationSources' => [
                    'one' => 'one.json'
                ],
                'one' => [
                    'key' => 'value'
                ]
            ],
            $configurationFactory()->all()
        );
    }
}
