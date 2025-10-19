<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(\App\Contracts\UserInterface::class, \App\Repositories\UserRepository::class);
        $this->app->bind(\App\Contracts\CombinedOrderInterface::class, \App\Repositories\CombinedOrderRepository::class);
        $this->app->bind(\App\Contracts\OrderInterface::class, \App\Repositories\OrderRepository::class);
        $this->app->bind(\App\Contracts\OrderPackageInterface::class, \App\Repositories\OrderPackageRepository::class);
        $this->app->bind(\App\Contracts\PackageItemInterface::class, \App\Repositories\PackageItemRepository::class);
    }


    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
