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
use N7e\Fixtures\ConfigurationSourceProducer;
use N7e\Fixtures\TestConfigurationSourceProducer;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConfigurationSourceProducerRegistry::class)]
class ConfigurationSourceProducerRegistryTest extends TestCase
{
    private ConfigurationSourceProducerRegistry $registry;

    #[Before]
    public function setUp(): void
    {
        $this->registry = new ConfigurationSourceProducerRegistry();
    }

    #[Test]
    public function shouldThrowExceptionIfNoProducersAreRegistered(): void
    {
        $this->expectException(UnsupportedConfigurationSourceException::class);

        $this->registry->configurationSourceFor('test');
    }

    #[Test]
    public function shouldThrowExceptionIfNoAppropriateProducerIsFound(): void
    {
        $this->expectException(UnsupportedConfigurationSourceException::class);

        $this->registry->register(new TestConfigurationSourceProducer([]));
        $this->registry->configurationSourceFor('unsupported://unsupported');
    }

    #[Test]
    public function shouldReturnResultFromLastRegisteredProducer(): void
    {
        $first = $this->getMockBuilder(ConfigurationSourceInterface::class)->getMock();
        $last = $this->getMockBuilder(ConfigurationSourceInterface::class)->getMock();

        $this->registry->register(new ConfigurationSourceProducer($first));
        $this->registry->register(new ConfigurationSourceProducer($last));

        $this->assertSame($last, $this->registry->configurationSourceFor('test://test'));
    }
}
