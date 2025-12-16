<?php

/*
 * Copyright (c) 2023 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace N7e\Fixtures;

use N7e\Configuration\ConfigurationSourceExceptionInterface;
use N7e\Configuration\ConfigurationSourceInterface;
use N7e\ConfigurationSourceProducerInterface;
use Override;
use RuntimeException;

class TestConfigurationSourceProducer implements ConfigurationSourceProducerInterface
{
    private readonly array $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public function canProduceConfigurationSourceFor(string $url): bool
    {
        return str_starts_with($url, 'test:');
    }

    public function produceConfigurationSourceFor(string $url): ConfigurationSourceInterface
    {
        return new class($url, $this->values) implements ConfigurationSourceInterface {
            private readonly string $url;
            private readonly array $values;

            public function __construct(string $url, array $values)
            {
                $this->url = $url;
                $this->values = $values;
            }

            #[Override]
            public function load(): array
            {
                parse_str(parse_url($this->url, PHP_URL_QUERY) ?? '', $parameters);

                if (array_key_exists('optional', $parameters)) {
                    throw new class extends RuntimeException implements ConfigurationSourceExceptionInterface { };
                }

                return $this->values;
            }
        };
    }
}
