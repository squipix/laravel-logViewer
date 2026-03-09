<?php

declare(strict_types=1);

namespace Squipix\LaravelLogViewer\Tests\Providers;

use Squipix\LaravelLogViewer\Contracts;
use Squipix\LaravelLogViewer\Providers\DeferredServicesProvider;
use Squipix\LaravelLogViewer\Tests\TestCase;

/**
 * Class     DeferredServicesProviderTest
 *
 * @author   SQUIPIX <info@squipix.com>
 */
class DeferredServicesProviderTest extends TestCase
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /** @var  \Squipix\LaravelLogViewer\Providers\DeferredServicesProvider */
    private $provider;

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    protected function setUp(): void
    {
        parent::setUp();

        $this->provider = $this->app->getProvider(DeferredServicesProvider::class);
    }

    protected function tearDown(): void
    {
        unset($this->provider);

        parent::tearDown();
    }

    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    /** @test */

    public function it_can_be_instantiated(): void
    {
        $expectations = [
            \Illuminate\Support\ServiceProvider::class,
            \Illuminate\Contracts\Support\DeferrableProvider::class,
            \Squipix\Support\Providers\ServiceProvider::class,
            DeferredServicesProvider::class,
        ];

        foreach ($expectations as $expected) {
            static::assertInstanceOf($expected, $this->provider);
        }
    }

    /** @test */
    public function it_can_provides(): void
    {
        $expected = [
            Contracts\LogViewer::class,
            Contracts\Utilities\LogLevels::class,
            Contracts\Utilities\LogStyler::class,
            Contracts\Utilities\LogMenu::class,
            Contracts\Utilities\Filesystem::class,
            Contracts\Utilities\Factory::class,
            Contracts\Utilities\LogChecker::class,
        ];

        static::assertSame($expected, $this->provider->provides());
    }
}
