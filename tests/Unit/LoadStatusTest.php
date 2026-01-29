<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Sunfox\ApcPdu\LoadStatus;

class LoadStatusTest extends TestCase
{
    public function testEnumValues(): void
    {
        $this->assertSame(1, LoadStatus::Normal->value);
        $this->assertSame(2, LoadStatus::LowLoad->value);
        $this->assertSame(3, LoadStatus::NearOverload->value);
        $this->assertSame(4, LoadStatus::Overload->value);
    }

    public function testAllCasesExist(): void
    {
        $cases = LoadStatus::cases();

        $this->assertCount(4, $cases);
        $this->assertContains(LoadStatus::Normal, $cases);
        $this->assertContains(LoadStatus::LowLoad, $cases);
        $this->assertContains(LoadStatus::NearOverload, $cases);
        $this->assertContains(LoadStatus::Overload, $cases);
    }

    public function testTryFromValidValues(): void
    {
        $this->assertSame(LoadStatus::Normal, LoadStatus::tryFrom(1));
        $this->assertSame(LoadStatus::LowLoad, LoadStatus::tryFrom(2));
        $this->assertSame(LoadStatus::NearOverload, LoadStatus::tryFrom(3));
        $this->assertSame(LoadStatus::Overload, LoadStatus::tryFrom(4));
    }

    public function testTryFromInvalidValue(): void
    {
        $this->assertNull(LoadStatus::tryFrom(0));
        $this->assertNull(LoadStatus::tryFrom(5));
    }
}
