<?php

use Squipix\LaravelLogViewer\Contracts;

if ( ! function_exists('log_viewer')) {
    /**
     * Get the LogViewer instance.
     *
     * @return Squipix\LaravelLogViewer\Contracts\LogViewer
     */
    function log_viewer()
    {
        return app(Contracts\LogViewer::class);
    }
}

if ( ! function_exists('log_levels')) {
    /**
     * Get the LogLevels instance.
     *
     * @return Squipix\LaravelLogViewer\Contracts\Utilities\LogLevels
     */
    function log_levels()
    {
        return app(Contracts\Utilities\LogLevels::class);
    }
}

if ( ! function_exists('log_menu')) {
    /**
     * Get the LogMenu instance.
     *
     * @return Squipix\LaravelLogViewer\Contracts\Utilities\LogMenu
     */
    function log_menu()
    {
        return app(Contracts\Utilities\LogMenu::class);
    }
}

if ( ! function_exists('log_styler')) {
    /**
     * Get the LogStyler instance.
     *
     * @return Squipix\LaravelLogViewer\Contracts\Utilities\LogStyler
     */
    function log_styler()
    {
        return app(Contracts\Utilities\LogStyler::class);
    }
}
