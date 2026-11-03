<?php

namespace Tests\Unit;

use App\Services\Financing\InstallmentSimulator;
use PHPUnit\Framework\TestCase;

class InstallmentSimulatorTest extends TestCase
{
    public function test_example_table_matches_the_nine_month_formula(): void
    {
        $simulator = new InstallmentSimulator();

        $classic = $simulator->quote('950', '280');
        $diamante = $simulator->quote('1400', '500');
        $tundra = $simulator->quote('1420', '450');

        $this->assertSame('670.00', $classic['balance']);
        $this->assertSame('74.44', $classic['monthly']);
        $this->assertSame('900.00', $diamante['balance']);
        $this->assertSame('100.00', $diamante['monthly']);
        $this->assertSame('970.00', $tundra['balance']);
        $this->assertSame('107.77', $tundra['monthly']);
        $this->assertSame(9, $classic['months']);
    }
}
