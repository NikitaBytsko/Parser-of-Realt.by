<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ParseClientServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('ParseClient','App\Services\ParseClient');
    }
}
