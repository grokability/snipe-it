<?php

namespace App\Providers;

use App\Services\Saml;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class SamlServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton(Saml::class, Saml::class);

        Route::group(['namespace'=> 'App\Http\Controllers'], function () {
            Route::group(['prefix'=> 'saml'], function () {
                Route::get(
                    'metadata',
                    [
                        'as' => 'saml.metadata',
                        'uses' => 'Auth\SamlController@metadata', ]
                );

                Route::match(
                    ['get', 'post'],
                    'acs',
                    [
                        'as' => 'saml.acs',
                        'uses' => 'Auth\SamlController@acs', ]
                );

                Route::get(
                    'sls',
                    [
                        'as' => 'saml.sls',
                        'uses' => 'Auth\SamlController@sls', ]
                );
            });

            Route::get(
                'login/saml',
                [
                    'as' => 'saml.login',
                    'uses' => 'Auth\SamlController@login', ]
            );

        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
    }
}
