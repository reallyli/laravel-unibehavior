<?php

namespace Reallyli\LaravelUnibehavior;

use Illuminate\Support\ServiceProvider;

class UnibehaviorServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/unibehavior.php' => config_path('unibehavior.php'),
        ], 'config');
        $this->mergeConfigFrom(__DIR__.'/../config/unibehavior.php', 'unibehavior');

        if (! class_exists('CreateBehaviorLogTable')) {
            $timestamp = date('Y_m_d_His', time());
            $this->publishes([
                __DIR__.'/../migrations/create_behavior_log_table.php.stub' => database_path("/migrations/{$timestamp}_create_behavior_log_table.php"),
            ], 'migrations');
        }

    }

    public function register()
    {
        $this->app->singleton('unibehavior', function ($app) {
            return new Unibehavior($app['auth'], $app['config'], $app['request']);
        });

        $this->app['router']->aliasMiddleware('unibehavior', UnibehaviorMiddleware::class);
    }
}