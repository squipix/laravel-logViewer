<?php

declare(strict_types=1);

namespace Squipix\LaravelLogViewer\Tests\Commands;

use Squipix\LaravelLogViewer\Tests\TestCase;

/**
 * Class     PublishCommandTest
 *
 * @author   SQUIPIX <info@squipix.com>
 */
class PublishCommandTest extends TestCase
{
    private bool $configExistsBefore = false;

    private bool $localizationsExistBefore = false;

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    protected function setUp(): void
    {
        parent::setUp();

        $this->configExistsBefore = $this->isConfigExists();
        $this->localizationsExistBefore = $this->getLocalizationFolder() !== false;
    }

    protected function tearDown(): void
    {
        $this->deleteConfig();
        $this->deleteLocalizations();

        parent::tearDown();
    }

    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    /** @test */
    public function it_can_publish_all(): void
    {
        $this->artisan('log-viewer:publish')
             ->assertSuccessful();

        static::assertHasConfigFile();
        static::assertHasLocalizationFiles();
        // TODO: Add views assertions
    }

    /** @test */
    public function it_can_publish_all_with_force(): void
    {
        $this->artisan('log-viewer:publish', ['--force'   => true])
             ->assertSuccessful();

        static::assertHasConfigFile();
        static::assertHasLocalizationFiles();
        // TODO: Add views assertions
    }

    /** @test */
    public function it_can_publish_only_config(): void
    {
        $this->artisan('log-viewer:publish', ['--tag' => 'config'])
             ->assertSuccessful();

        static::assertHasConfigFile();
        static::assertHasNotLocalizationFiles();
        // TODO: Add views assertions
    }

    /**
     * @test
     *
     * @dataProvider  providePublishableTranslationsTags
     *
     * @param  string  $tag
     */
    public function it_can_publish_only_translations(string $tag): void
    {
        $this->artisan('log-viewer:publish', ['--tag' => $tag])
             ->assertExitCode(0);

        static::assertHasNotConfigFile();
        static::assertHasLocalizationFiles();
        // TODO: Add views assertions
    }

    public static function providePublishableTranslationsTags(): array
    {
        return [
            ['translations'],
            ['log-viewer-translations'],
        ];
    }

    /* -----------------------------------------------------------------
     |  Custom Assertions
     | -----------------------------------------------------------------
     */

    /**
     * Assert config file publishes
     */
    protected function assertHasConfigFile(): void
    {
        static::assertFileExists($this->getConfigFilePath());
        static::assertTrue($this->isConfigExists());
    }

    /**
     * Assert config file publishes
     */
    protected function assertHasNotConfigFile(): void
    {
        if ($this->configExistsBefore) {
            static::assertFileExists($this->getConfigFilePath());
            static::assertTrue($this->isConfigExists());
        } else {
            static::assertFileDoesNotExist($this->getConfigFilePath());
            static::assertFalse($this->isConfigExists());
        }
    }

    /**
     * Assert lang files publishes
     */
    protected function assertHasLocalizationFiles(): void
    {
        $path        = $this->getLocalizationFolder();
        $directories = $this->illuminateFile()->directories($path);
        $locales     = array_map('basename', $directories);

        static::assertEmpty(
            $missing = array_diff($locales, static::$locales),
            'The locales ['.implode(', ', $missing).'] are missing in the Squipix\\LaravelLogViewer\\Tests\\TestCase::$locales (line 29) for tests purposes.'
        );

        foreach ($directories as $directory) {
            static::assertFileExists($directory . '/levels.php');
        }
    }

    /**
     * Assert lang files publishes
     */
    protected function assertHasNotLocalizationFiles(): void
    {
        if ($this->localizationsExistBefore) {
            static::assertNotFalse($this->getLocalizationFolder());
        } else {
            static::assertFalse($this->getLocalizationFolder());
        }
    }

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
     */

    private function deleteConfig(): void
    {
        $config = $this->getConfigFilePath();

        if (! $this->configExistsBefore && $this->isConfigExists()) {
            $this->illuminateFile()->delete($config);
        }
    }

    /**
     * Check if LogViewer config file exists.
     */
    private function isConfigExists(): bool
    {
        $path = $this->getConfigFilePath();

        return $this->illuminateFile()->exists($path);
    }

    /**
     * Get LogViewer config file path.
     */
    private function getConfigFilePath(): string
    {
        return $this->getConfigPath().'/log-viewer.php';
    }

    /**
     * Get LogViewer lang folder
     */
    private function getLocalizationFolder(): string|false
    {
        return realpath(lang_path('vendor/log-viewer'));
    }

    /**
     * Delete lang folder
     */
    private function deleteLocalizations(): void
    {
        $path = $this->getLocalizationFolder();

        if (! $this->localizationsExistBefore && $path) {
            $this->illuminateFile()->deleteDirectory(dirname($path));
        }
    }
}
