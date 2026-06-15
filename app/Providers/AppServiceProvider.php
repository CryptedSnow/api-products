<?php

namespace App\Providers;

use App\Interfaces\{AuthInterface, ProdutoInterface};
use App\Services\{AuthService, ProdutoService};
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuthInterface::class, AuthService::class);
        $this->app->bind(ProdutoInterface::class, ProdutoService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
