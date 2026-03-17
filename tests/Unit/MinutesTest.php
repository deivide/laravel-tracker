<?php

namespace PragmaRX\Tracker\Tests\Unit;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use PragmaRX\Tracker\Support\Minutes;

class MinutesTest extends TestCase
{
    /** @test */
    public function it_creates_from_integer()
    {
        $minutes = Minutes::make(60);

        $this->assertInstanceOf(Minutes::class, $minutes);
        $this->assertEquals(60, $minutes->getMinutes());
    }

    /** @test */
    public function it_copies_start_end_from_minutes_instance()
    {
        $original = new Minutes();
        $original->setStart(Carbon::parse('2026-01-01'));
        $original->setEnd(Carbon::parse('2026-01-31'));

        $copy = Minutes::make($original);

        $this->assertEquals($original->getStart(), $copy->getStart());
        $this->assertEquals($original->getEnd(), $copy->getEnd());
    }

    /** @test */
    public function it_calculates_start_end_from_integer()
    {
        $minutes = Minutes::make(120);

        $this->assertNotNull($minutes->getStart());
        $this->assertNotNull($minutes->getEnd());
    }

    /** @test */
    public function zero_minutes_sets_today_range()
    {
        $minutes = Minutes::make(0);

        $this->assertEquals(Carbon::now()->startOfDay()->format('Y-m-d'), $minutes->getStart()->format('Y-m-d'));
        $this->assertEquals(Carbon::now()->format('Y-m-d'), $minutes->getEnd()->format('Y-m-d'));
    }
}
