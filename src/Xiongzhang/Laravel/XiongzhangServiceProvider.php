<?php

namespace Xiongzhang\Laravel;

use Xiongzhang\Server as Xiongzhang;
use Illuminate\Support\ServiceProvider;

class XiongzhangServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Xiongzhang::class, function ($app) {
            $xzh = Xiongzhang::init(config('xiongzhang'));
            return $xzh;
        });
        $this->app->alias(Xiongzhang::class, 'xiongzhang');
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        $this->setupConfig();
    }

    /**
     * Setup the config.
     *
     * @return void
     */
    protected function setupConfig()
    {
        $source = realpath(__DIR__.'/config.php');
        if ($this->app instanceof LaravelApplication) {
            if ($this->app->runningInConsole()) {
                $this->publishes([
                    $source => config_path('xiongzhang.php'),
                ]);
            }

        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('xiongzhang');
        }

        $this->mergeConfigFrom($source, 'xiongzhang');
    }

    /**
     * Get config value by key.
     *
     * @param string $key
     * @param mixed|null $default
     *
     * @return mixed
     */
    private function config($key, $default = null)
    {
        return $this->app->make('config')->get("xiongzhang.{$key}", $default);
    }
}
