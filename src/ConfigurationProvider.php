<?php

/*
 * Copyright (c) 2025 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N7e;

use N7e\Configuration\ConfigurationBuilder;
use N7e\Configuration\ConfigurationInterface;
use N7e\Configuration\JsonConfigurationSource;
use N7e\DependencyInjection\ContainerBuilderInterface;
use N7e\DependencyInjection\ContainerInterface;
use Override;

/**
 * Provides flexible application configuration.
 */
final class ConfigurationProvider implements ServiceProviderInterface
{
    /**
     * Configuration manager.
     *
     * @var \N7e\ConfigurationManager
     */
    private ConfigurationManager $configurationManager;

    #[Override]
    public function configure(ContainerBuilderInterface $containerBuilder): void
    {
        /** @var \N7e\RootDirectoryAggregateInterface $rootDirectoryAggregate */
        $rootDirectoryAggregate = $containerBuilder->build()->get(RootDirectoryAggregateInterface::class);
        $configurationBuilder = (new ConfigurationBuilder())
            ->addConfigurationSource(
                new JsonConfigurationSource($rootDirectoryAggregate->getRootDirectory() . '/configuration.json')
            );
        $configuration = $configurationBuilder->build();

        /** @var string[] $configurationSources */
        $configurationSources = $configuration->get('configurationSources', []);

        foreach ($configurationSources as $keyPath => $configurationSource) {
            $configurationBuilder->addConfigurationSource(
                new JsonConfigurationSource($rootDirectoryAggregate->getRootDirectory() . '/' . $configurationSource),
                $keyPath
            );
        }

        $this->configurationManager = new ConfigurationManager($configurationBuilder->build());

        $containerBuilder->addFactory(ConfigurationManagerInterface::class, fn() => $this->configurationManager)
            ->singleton()
            ->alias('configuration-manager');
        $containerBuilder->addFactory(ConfigurationInterface::class, [$this->configurationManager, 'current'])
            ->alias('configuration');
    }

    /**
     * {@inheritDoc}
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    #[Override]
    public function load(ContainerInterface $container): void
    {
    }
}
