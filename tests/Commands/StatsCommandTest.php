<?php

declare(strict_types=1);

namespace Squipix\LaravelLogViewer\Tests\Commands;

use Squipix\LaravelLogViewer\Tests\TestCase;

/**
 * Class     StatsCommandTest
 *
 * @author   SQUIPIX <info@squipix.com>
 */
class StatsCommandTest extends TestCase
{
    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    /** @test */
    public function it_can_display_stats(): void
    {
        $this->artisan('log-viewer:stats')
             ->assertExitCode(0);
    }
}
