<?php

namespace PragmaRX\Tracker\Tests\Feature;

use PragmaRX\Tracker\Tests\EnabledTestCase;
use PragmaRX\Tracker\Tracker;

class TrackerEnabledTest extends EnabledTestCase
{
    /** @test */
    public function it_resolves_tracker_from_container()
    {
        $tracker = $this->app->make('tracker');

        $this->assertInstanceOf(Tracker::class, $tracker);
    }

    /** @test */
    public function tracker_is_enabled_by_default()
    {
        $tracker = $this->app->make('tracker');

        $this->assertTrue($tracker->isEnabled());
    }

    /** @test */
    public function tracker_can_be_turned_off()
    {
        $tracker = $this->app->make('tracker');

        $tracker->turnOff();

        $this->assertFalse($tracker->isEnabled());
    }

    /** @test */
    public function it_is_not_trackable_when_log_disabled()
    {
        $tracker = $this->app->make('tracker');

        $this->assertFalse($tracker->isTrackable());
    }
}
