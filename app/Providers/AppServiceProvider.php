<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Prasarana;
use App\Observers\PrasaranaObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Prasarana::observe(PrasaranaObserver::class);
    }
}
