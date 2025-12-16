<?php

/*
 * Copyright (c) 2025 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N7e;

use Closure;
use N7e\Configuration\ConfigurationBuilderInterface;
use N7e\Configuration\ConfigurationInterface;
use N7e\Configuration\ConfigurationManagerInterface;
use N7e\DependencyInjection\ContainerBuilderInterface;
use N7e\DependencyInjection\ContainerInterface;
use N7e\DependencyInjection\DependencyDefinitionInterface;
use N7e\fixtures\TestConfigurationSourceProducer;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(ConfigurationProvider::class)]
final class ConfigurationProviderTest extends TestCase
{
    private ConfigurationProvider $provider;
    private MockObject $containerBuilderMock;
    private MockObject $containerMock;

    /**
     * Capture dependency injection container builder factories.
     *
     * @param array $identifiers Identifiers of factories to capture.
     * @param array $factories Reference to captured factories.
     * @return \Closure Closure used in returned callback.
     */
    private function captureFactoriesFor(array $identifiers, array &$factories): Closure
    {
        return function ($identifier, $factory) use ($identifiers, &$factories) {
            if (in_array($identifier, $identifiers)) {
                $factories[] = $factory;
            }

            return $this->getMockBuilder(DependencyDefinitionInterface::class)->getMock();
        };
    }

    #[Before]
    public function setUp(): void
    {
        $this->provider = new ConfigurationProvider([]);
        $this->containerBuilderMock = $this->getMockBuilder(ContainerBuilderInterface::class)->getMock();
        $this->containerMock = $this->getMockBuilder(ContainerInterface::class)->getMock();
    }

    #[Test]
    public function shouldRegisterNecessaryConfigurationComponents(): void
    {
        $matcher = $this->exactly(4);

        $this->containerBuilderMock
            ->expects($matcher)
            ->method('addFactory')
            ->willReturnCallback(function ($identifier, $factory) use ($matcher) {
                switch ($matcher->numberOfInvocations()) {
                    case 1:
                        $this->assertEquals(ConfigurationBuilderInterface::class, $identifier);
                        $this->assertIsCallable($factory);
                        break;
                    case 2:
                        $this->assertEquals(ConfigurationSourceProducerRegistryInterface::class, $identifier);
                        $this->assertIsCallable($factory);
                        break;
                    case 3:
                        $this->assertEquals(ConfigurationManagerInterface::class, $identifier);
                        $this->assertIsCallable($factory);
                        break;
                    case 4:
                        $this->assertEquals(ConfigurationInterface::class, $identifier);
                        $this->assertIsCallable($factory);
                        break;
                }

                return $this->getMockBuilder(DependencyDefinitionInterface::class)->getMock();
            });

        $this->provider->configure($this->containerBuilderMock);
    }

    #[Test]
    public function shouldThrowExceptionIfAccessingCertainComponentBeforeLoadPhase(): void
    {
        $factories = [];
        $this->containerBuilderMock
            ->method('addFactory')
            ->willReturnCallback(
                $this->captureFactoriesFor(
                    [
                        ConfigurationManagerInterface::class,
                        ConfigurationInterface::class
                    ],
                    $factories
                )
            );

        $this->provider->configure($this->containerBuilderMock);

        foreach ($factories as $factory) {
            $expectedException = null;

            try {
                $factory();
            } catch (RuntimeException $exception) {
                $expectedException = $exception;
            }

            $this->assertInstanceOf(RuntimeException::class, $expectedException);
        }
    }

    #[Test]
    public function shouldNotThrowExceptionIfAccessingComponentsAfterLoadPhase(): void
    {
        $this->expectNotToPerformAssertions();

        $factories = [];
        $this->containerBuilderMock
            ->method('addFactory')
            ->willReturnCallback(
                $this->captureFactoriesFor(
                    [
                        ConfigurationManagerInterface::class,
                        ConfigurationInterface::class
                    ],
                    $factories
                )
            );

        $this->provider->configure($this->containerBuilderMock);
        $this->provider->load($this->containerMock);

        foreach ($factories as $factory) {
            $factory();
        }
    }

    #[Test]
    public function shouldAddConfigurationSourcesFromInitialConfiguration(): void
    {
        $provider = new ConfigurationProvider(
            [
                'configuration' => [
                    'sources' => [
                        'test://test'
                    ]
                ]
            ]
        );

        $factories = [];
        $this->containerBuilderMock
            ->method('addFactory')
            ->willReturnCallback(
                $this->captureFactoriesFor(
                    [
                        ConfigurationSourceProducerRegistryInterface::class,
                        ConfigurationInterface::class
                    ],
                    $factories
                )
            );

        $provider->configure($this->containerBuilderMock);

        [$configurationSourceProducerRegistryFactory, $configurationFactory] = $factories;

        /** @var \N7e\ConfigurationSourceProducerRegistryInterface $configurationSourceProducers */
        $configurationSourceProducers = $configurationSourceProducerRegistryFactory();

        $configurationSourceProducers->register(new TestConfigurationSourceProducer(['key' => 'value']));

        $provider->load($this->containerMock);

        /** @var \N7e\Configuration\ConfigurationInterface $configuration */
        $configuration = $configurationFactory();

        $this->assertEquals('value', $configuration->get('key'));
    }

    #[Test]
    public function shouldPropagateConfigurationSourceKeyPath(): void
    {
        $provider = new ConfigurationProvider(
            [
                'configuration' => [
                    'sources' => [
                        'test://test?keyPath=nested'
                    ]
                ]
            ]
        );

        $factories = [];
        $this->containerBuilderMock
            ->method('addFactory')
            ->willReturnCallback(
                $this->captureFactoriesFor(
                    [
                        ConfigurationSourceProducerRegistryInterface::class,
                        ConfigurationInterface::class
                    ],
                    $factories
                )
            );

        $provider->configure($this->containerBuilderMock);

        [$configurationSourceProducerRegistryFactory, $configurationFactory] = $factories;

        /** @var \N7e\ConfigurationSourceProducerRegistryInterface $configurationSourceProducers */
        $configurationSourceProducers = $configurationSourceProducerRegistryFactory();

        $configurationSourceProducers->register(new TestConfigurationSourceProducer(['key' => 'value']));

        $provider->load($this->containerMock);

        /** @var \N7e\Configuration\ConfigurationInterface $configuration */
        $configuration = $configurationFactory();

        $this->assertEquals(['key' => 'value'], $configuration->get('nested'));
    }

    #[Test]
    public function shouldPropagateConfigurationSourceOptionalFlag(): void
    {
        $provider = new ConfigurationProvider(
            [
                'configuration' => [
                    'sources' => [
                        'test://test?optional'
                    ]
                ]
            ]
        );

        $factories = [];
        $this->containerBuilderMock
            ->method('addFactory')
            ->willReturnCallback(
                $this->captureFactoriesFor(
                    [
                        ConfigurationSourceProducerRegistryInterface::class,
                        ConfigurationInterface::class
                    ],
                    $factories
                )
            );

        $provider->configure($this->containerBuilderMock);

        [$configurationSourceProducerRegistryFactory, $configurationFactory] = $factories;

        /** @var \N7e\ConfigurationSourceProducerRegistryInterface $configurationSourceProducers */
        $configurationSourceProducers = $configurationSourceProducerRegistryFactory();

        $configurationSourceProducers->register(new TestConfigurationSourceProducer(['key' => 'value']));

        $provider->load($this->containerMock);

        /** @var \N7e\Configuration\ConfigurationInterface $configuration */
        $configuration = $configurationFactory();

        $this->assertFalse($configuration->has('key'));
    }

    #[Test]
    public function shouldThrowExceptionIfInvalidConfigurationSourceUrl(): void
    {
        $this->expectException(RuntimeException::class);

        $provider = new ConfigurationProvider(
            [
                'configuration' => [
                    'sources' => [
                        'invalid'
                    ]
                ]
            ]
        );

        $provider->configure($this->containerBuilderMock);
        $provider->load($this->containerMock);
    }
}
