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
 * Has the ability to produce configuration sources.
 */
interface ConfigurationSourceProducerInterface
{
    /**
     * Determine whether this producer can produce a configuration source for a given URL.
     *
     * @param string $url Arbitrary configuration source URL.
     * @return bool True if this producer can produce a configuration source for the given URL.
     */
    public function canProduceConfigurationSourceFor(string $url): bool;

    /**
     * Produce a configurations source for a given URL.
     *
     * @param string $url Arbitrary configuration source URL.
     * @return \N7e\Configuration\ConfigurationSourceInterface Appropriate configuration source.
     */
    public function produceConfigurationSourceFor(string $url): ConfigurationSourceInterface;
}
