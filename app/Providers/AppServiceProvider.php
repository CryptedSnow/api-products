<?php

namespace App\Providers;

use App\Repositories\{AuthRepository, ProdutoRepository};
use App\Services\{AuthService, ProdutoService};
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuthRepository::class, AuthService::class);
        $this->app->bind(ProdutoRepository::class, ProdutoService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
