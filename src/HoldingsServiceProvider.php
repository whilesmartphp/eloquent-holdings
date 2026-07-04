<?php

namespace Whilesmart\Holdings;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Whilesmart\Holdings\Console\RepriceHoldingsCommand;
use Whilesmart\Holdings\Contracts\HoldingPriceProvider;
use Whilesmart\Holdings\Contracts\ResponseFormatter;
use Whilesmart\Holdings\PriceProviders\NullPriceProvider;
use Whilesmart\Holdings\ResponseFormatters\DefaultResponseFormatter;

class HoldingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/holdings.php', 'holdings');

        // Host apps bind their own provider (e.g. a CoinGecko adapter). Without
        // one, "auto" holdings simply keep their last price.
        $this->app->bindIf(HoldingPriceProvider::class, NullPriceProvider::class);

        $this->app->bind(ResponseFormatter::class, function () {
            return new (config('holdings.response_formatter') ?: DefaultResponseFormatter::class);
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishes([
            __DIR__.'/../config/holdings.php' => config_path('holdings.php'),
        ], 'holdings-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'holdings-migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([RepriceHoldingsCommand::class]);
        }

        if (config('holdings.register_routes', true)) {
            Route::middleware(config('holdings.route_middleware', ['api', 'auth:sanctum']))
                ->prefix(config('holdings.route_prefix', 'api'))
                ->group(__DIR__.'/../routes/api.php');
        }
    }
}
