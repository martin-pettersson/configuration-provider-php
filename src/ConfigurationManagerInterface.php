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

/**
 * Has the ability to manage a configuration object.
 */
interface ConfigurationManagerInterface
{
    /**
     * Replace current configuration object with a given configuration object.
     *
     * @param \N7e\Configuration\ConfigurationInterface $configuration Arbitrary configuration object.
     */
    public function replaceWith(ConfigurationInterface $configuration): void;
}
