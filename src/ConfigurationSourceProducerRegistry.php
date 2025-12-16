<?php

/*
 * Copyright (c) 2025 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N7e;

use N7e\Configuration\ConfigurationSourceInterface;
use Override;

/**
 * Implementation of {@see \N7e\ConfigurationSourceProducerRegistryInterface}.
 */
final class ConfigurationSourceProducerRegistry implements ConfigurationSourceProducerRegistryInterface
{
    /**
     * Registered producers.
     *
     * @var \N7e\ConfigurationSourceProducerInterface[]
     */
    private array $producers = [];

    #[Override]
    public function register(ConfigurationSourceProducerInterface $producer): void
    {
        array_unshift($this->producers, $producer);
    }

    #[Override]
    public function configurationSourceFor(string $url): ConfigurationSourceInterface
    {
        foreach ($this->producers as $producer) {
            if ($producer->canProduceConfigurationSourceFor($url)) {
                return $producer->produceConfigurationSourceFor($url);
            }
        }

        throw new UnsupportedConfigurationSourceException($url);
    }
}
