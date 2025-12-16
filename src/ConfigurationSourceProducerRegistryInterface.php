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

/**
 * Represents a set of registered configuration source producers.
 */
interface ConfigurationSourceProducerRegistryInterface
{
    /**
     * Register given configuration source producer.
     *
     * @param \N7e\ConfigurationSourceProducerInterface $producer Arbitrary configuration source producer.
     */
    public function register(ConfigurationSourceProducerInterface $producer): void;

    /**
     * Produce a configuration source for a given URL.
     *
     * @param string $url Arbitrary configuration source URL.
     * @return \N7e\Configuration\ConfigurationSourceInterface Appropriate configuration source.
     */
    public function configurationSourceFor(string $url): ConfigurationSourceInterface;
}
