<?php

/*
 * Copyright (c) 2025 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N7e;

use N7e\Configuration\ArrayConfigurationSource;
use N7e\Configuration\ConfigurationBuilder;
use N7e\Configuration\ConfigurationBuilderInterface;
use N7e\Configuration\ConfigurationInterface;
use N7e\Configuration\ConfigurationManager;
use N7e\Configuration\ConfigurationManagerInterface;
use N7e\DependencyInjection\ContainerBuilderInterface;
use N7e\DependencyInjection\ContainerInterface;
use Override;
use RuntimeException;

/**
 * Provides flexible application configuration.
 */
final class ConfigurationProvider implements ServiceProviderInterface
{
    /**
     * Initial configuration.
     *
     * @var array
     */
    private readonly array $initialConfiguration;

    /**
     * Configuration builder.
     *
     * @var \N7e\Configuration\ConfigurationBuilderInterface
     */
    private readonly ConfigurationBuilderInterface $configurationBuilder;

    /**
     * Registered configuration source producers.
     *
     * @var \N7e\ConfigurationSourceProducerRegistryInterface
     */
    private readonly ConfigurationSourceProducerRegistryInterface $configurationSourceProducers;

    /**
     * Configuration manager.
     *
     * @var \N7e\Configuration\ConfigurationManager|null
     */
    private ?ConfigurationManager $configurationManager = null;

    /**
     * Create a new service provider instance.
     *
     * @param array $initialConfiguration Initial configuration.
     */
    public function __construct(array $initialConfiguration)
    {
        $this->initialConfiguration = $initialConfiguration;
        $this->configurationBuilder = new ConfigurationBuilder();
        $this->configurationSourceProducers = new ConfigurationSourceProducerRegistry();
    }

    #[Override]
    public function configure(ContainerBuilderInterface $containerBuilder): void
    {
        $containerBuilder->addFactory(ConfigurationBuilderInterface::class, fn() => $this->configurationBuilder)
            ->singleton()
            ->alias('configuration-builder');
        $containerBuilder->addFactory(
            ConfigurationSourceProducerRegistryInterface::class,
            fn() => $this->configurationSourceProducers
        )
            ->singleton()
            ->alias('configuration-source-producer-registry');
        $containerBuilder->addFactory(ConfigurationManagerInterface::class, function () {
            if (is_null($this->configurationManager)) {
                throw new RuntimeException(
                    'Cannot use configuration manager before the configuration provider\'s load phase'
                );
            }

            return $this->configurationManager;
        })
            ->singleton()
            ->alias('configuration-manager');
        $containerBuilder->addFactory(ConfigurationInterface::class, function () {
            if (is_null($this->configurationManager)) {
                throw new RuntimeException(
                    'Cannot use configuration before the configuration provider\'s load phase'
                );
            }

            return $this->configurationManager->current();
        })
            ->singleton()
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
        $this->configurationBuilder->addConfigurationSource(new ArrayConfigurationSource($this->initialConfiguration));

        foreach ($this->initialConfiguration['configuration']['sources'] ?? [] as $configurationSource) {
            if (filter_var($configurationSource, FILTER_VALIDATE_URL) === false) {
                throw new RuntimeException("Invalid configuration source URL: {$configurationSource}");
            }

            parse_str((string) parse_url($configurationSource, PHP_URL_QUERY), $parameters);

            $definition = $this->configurationBuilder->addConfigurationSource(
                $this->configurationSourceProducers->configurationSourceFor($configurationSource)
            );

            if (array_key_exists('keyPath', $parameters)) {
                /** @var array<string, string> $parameters */
                $definition->atKeyPath($parameters['keyPath']);
            }

            if (array_key_exists('optional', $parameters)) {
                $definition->optional();
            }
        }

        $this->configurationManager = new ConfigurationManager($this->configurationBuilder->build());
    }
}
