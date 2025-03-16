<?php

/*
 * Copyright (c) 2025 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N7e;

use N7e\Configuration\ConfigurationInterface;
use Override;

/**
 * An implementation of {@see \N7e\ConfigurationManagerInterface}.
 */
final class ConfigurationManager implements ConfigurationManagerInterface
{
    /**
     * Managed configuration object.
     *
     * @var \N7e\Configuration\ConfigurationInterface
     */
    private ConfigurationInterface $configuration;

    /**
     * Create a new configuration manager instance.
     *
     * @param \N7e\Configuration\ConfigurationInterface $configuration Currently managed configuration object.
     */
    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    #[Override]
    public function replaceWith(ConfigurationInterface $configuration): void
    {
        $this->configuration = $configuration;
    }

    /**
     * Retrieve currently managed configuration object.
     *
     * @return \N7e\Configuration\ConfigurationInterface Currently managed configuration object.
     */
    public function current(): ConfigurationInterface
    {
        return $this->configuration;
    }
}
