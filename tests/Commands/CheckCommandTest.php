<?php

declare(strict_types=1);

namespace Squipix\LaravelLogViewer\Tests\Commands;

use Squipix\LaravelLogViewer\Tests\TestCase;

/**
 * Class     CheckCommandTest
 *
 * @author   SQUIPIX <info@squipix.com>
 */
class CheckCommandTest extends TestCase
{
    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    /** @test */
    public function it_can_check(): void
    {
        $this->artisan('log-viewer:check')
             ->assertExitCode(0);
    }
}
