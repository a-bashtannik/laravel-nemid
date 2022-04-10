<?php

declare(strict_types=1);

namespace Rentdesk\Nemid\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Rentdesk\Nemid\Services\NemidRevocationChecker;
use Rentdesk\Nemid\Services\NemidService;

class NemidServiceProvider extends ServiceProvider
{
    protected function registerRoutes(): void
    {
        Route::group(
            [
                'middleware' => Config::get('nemid.middleware', [])
            ],
            function () {
                $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');

                if (config('app.debug')) {
                    Route::get(
                        'nemid/debug',
                        function () {
                            return view('nemid::debug');
                        }
                    );
                }
            }
        );
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/config.php', 'nemid');

        $this->app->bind(
            'nemid',
            function () {
                return new NemidService(
                    Config::get('nemid.sp_id'),
                    File::get(Config::get('nemid.sp_key')),
                    File::get(Config::get('nemid.sp_cert')),
                    Config::get('app.name', ''),
                    Config::get('app.locale', 'en'),
                    Config::get('app.debug')
                );
            }
        );

        $this->app->bind(
            NemidRevocationChecker::class,
            function () {
                return new NemidRevocationChecker(
                    Config::get('nemid.crl_check_enabled', false),
                    Config::get('nemid.ocsp_check_enabled', false),
                );
            }
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes(
                [
                    __DIR__ . '/../../config/config.php' => config_path('nemid.php'),
                ],
                'config'
            );

            $this->publishes(
                [
                    __DIR__ . '/../../resources/js/vue-nemid/src/components' =>
                        resource_path(
                            'assets/js/vue-nemid/components'
                        )
                ],
                'vue-components'
            );
        }

        if (config('app.debug')) {
            $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'nemid');
        }

        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'nemid');

        $this->registerRoutes();
    }
}
