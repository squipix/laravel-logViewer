<?php

declare(strict_types=1);

namespace Squipix\LaravelLogViewer\Utilities;

use Squipix\LaravelLogViewer\Contracts\Utilities\LogMenu as LogMenuContract;
use Squipix\LaravelLogViewer\Contracts\Utilities\LogStyler as LogStylerContract;
use Squipix\LaravelLogViewer\Entities\Log;
use Illuminate\Contracts\Config\Repository as ConfigContract;

/**
 * Class     LogMenu
 *
 * @author   SQUIPIX <info@squipix.com>
 */
class LogMenu implements LogMenuContract
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /**
     * The config repository instance.
     *
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * The log styler instance.
     *
     * @var \Squipix\LaravelLogViewer\Contracts\Utilities\LogStyler
     */
    private $styler;

    /* -----------------------------------------------------------------
     |  Constructor
     | -----------------------------------------------------------------
     */

    /**
     * LogMenu constructor.
     *
     * @param  \Illuminate\Contracts\Config\Repository             $config
     * @param  \Squipix\LaravelLogViewer\Contracts\Utilities\LogStyler  $styler
     */
    public function __construct(ConfigContract $config, LogStylerContract $styler)
    {
        $this->setConfig($config);
        $this->setLogStyler($styler);
    }

    /* -----------------------------------------------------------------
     |  Getters & Setters
     | -----------------------------------------------------------------
     */

    /**
     * Set the config instance.
     *
     * @param  \Illuminate\Contracts\Config\Repository  $config
     *
     * @return self
     */
    public function setConfig(ConfigContract $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Set the log styler instance.
     *
     * @param  \Squipix\LaravelLogViewer\Contracts\Utilities\LogStyler  $styler
     *
     * @return self
     */
    public function setLogStyler(LogStylerContract $styler)
    {
        $this->styler = $styler;

        return $this;
    }

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Make log menu.
     *
     * @param  \Squipix\LaravelLogViewer\Entities\Log  $log
     * @param  bool                               $trans
     *
     * @return array
     */
    public function make(Log $log, $trans = true)
    {
        $items = [];
        $route = $this->config('menu.filter-route');

        foreach($log->tree($trans) as $level => $item) {
            $items[$level] = array_merge($item, [
                'url'  => route($route, [$log->date, $level]),
                'icon' => $this->isIconsEnabled() ? $this->styler->icon($level)->toHtml() : '',
            ]);
        }

        return $items;
    }

    /* -----------------------------------------------------------------
     |  Check Methods
     | -----------------------------------------------------------------
     */

    /**
     * Check if the icons are enabled.
     *
     * @return bool
     */
    private function isIconsEnabled()
    {
        return (bool) $this->config('menu.icons-enabled', false);
    }

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get config.
     *
     * @param  string  $key
     * @param  mixed   $default
     *
     * @return mixed
     */
    private function config($key, $default = null)
    {
        return $this->config->get("log-viewer.$key", $default);
    }
}
