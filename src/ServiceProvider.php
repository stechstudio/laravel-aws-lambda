<?php

namespace STS\Serverless;

use STS\Serverless\Console\Install;
use STS\Serverless\Console\Package;
use STS\Serverless\Console\Test;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    protected $commandList = [
        Test::class,
        Install::class,
        Package::class
    ];

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Default path to configuration
     * @var string
     */
    protected $configPath = __DIR__ . '/../config/serverless.php';

    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        require('helpers.php');

        // helps deal with Lumen vs Laravel differences
        if (function_exists('config_path')) {
            $publishPath = config_path('serverless.php');
        } else {
            $publishPath = base_path('config/serverless.php');
        }
        $this->publishes([$this->configPath => $publishPath], 'config');
    }
    /**
     * Register the service provider.
     */
    public function register()
    {
        if (is_a($this->app, 'Laravel\Lumen\Application')) {
            $this->app->configure('serverless');
        }
        $this->mergeConfigFrom($this->configPath, 'serverless');
        $this->commands($this->commandList);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
