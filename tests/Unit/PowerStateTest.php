<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Sunfox\ApcPdu\PowerState;

class PowerStateTest extends TestCase
{
    public function testEnumValues(): void
    {
        $this->assertSame(1, PowerState::Off->value);
        $this->assertSame(2, PowerState::On->value);
    }

    public function testAllCasesExist(): void
    {
        $cases = PowerState::cases();

        $this->assertCount(2, $cases);
        $this->assertContains(PowerState::Off, $cases);
        $this->assertContains(PowerState::On, $cases);
    }

    public function testTryFromValidValues(): void
    {
        $this->assertSame(PowerState::Off, PowerState::tryFrom(1));
        $this->assertSame(PowerState::On, PowerState::tryFrom(2));
    }

    public function testTryFromInvalidValue(): void
    {
        $this->assertNull(PowerState::tryFrom(0));
        $this->assertNull(PowerState::tryFrom(3));
    }
}
