<?php

namespace App\Providers;

use App\Services\SnipeTranslator;
use Illuminate\Translation\TranslationServiceProvider;

class SnipeTranslationServiceProvider extends TranslationServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //This is almost an *EXACT* carbon-copy of the TranslationServiceProvider, except with a modified Translator
        $this->registerLoader();

        $this->app->singleton('translator', function ($app) {
            $loader = $app['translation.loader'];

            // When registering the translator component, we'll need to set the default
            // locale as well as the fallback locale. So, we'll grab the application
            // configuration so we can easily get both of these values from there.
            $locale = $app['config']['app.locale'];

            $trans = new SnipeTranslator($loader, $locale); //the ONLY changed line

            $trans->setFallback($app['config']['app.fallback_locale']);

            return $trans;
        });
    }
}
