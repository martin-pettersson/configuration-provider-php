<?php

/*
 * Copyright (c) 2025 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N7e\Fixtures;

use N7e\Configuration\ConfigurationSourceInterface;
use N7e\ConfigurationSourceProducerInterface;
use Override;

class ConfigurationSourceProducer implements ConfigurationSourceProducerInterface
{
    private readonly ConfigurationSourceInterface $configurationSource;

    public function __construct(ConfigurationSourceInterface $configurationSource)
    {
        $this->configurationSource = $configurationSource;
    }

    #[Override]
    public function canProduceConfigurationSourceFor(string $url): bool
    {
        return true;
    }

    #[Override]
    public function produceConfigurationSourceFor(string $url): ConfigurationSourceInterface
    {
        return $this->configurationSource;
    }
}
