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
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConfigurationManager::class)]
final class ConfigurationManagerTest extends TestCase
{
    private MockObject $configurationMock;
    private ConfigurationManager $configurationManager;

    #[Before]
    public function setUp(): void
    {
        $this->configurationMock = $this->getMockBuilder(ConfigurationInterface::class)->getMock();
        $this->configurationManager = new ConfigurationManager($this->configurationMock);
    }

    #[Test]
    public function shouldProvideManagedConfigurationObject(): void
    {
        $this->assertSame($this->configurationMock, $this->configurationManager->current());
    }

    #[Test]
    public function shouldReplaceManagedConfigurationObject(): void
    {
        $configurationMock = $this->getMockBuilder(ConfigurationInterface::class)->getMock();

        $this->configurationManager->replaceWith($configurationMock);

        $this->assertSame($configurationMock, $this->configurationManager->current());
        $this->assertNotSame($this->configurationMock, $this->configurationManager->current());
    }
}
