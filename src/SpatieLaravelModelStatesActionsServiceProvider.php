<?php

namespace Abather\SpatieLaravelModelStatesActions;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SpatieLaravelModelStatesActionsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('spatie-laravel-model-states-actions');
    }

    public function packageBooted(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'spatie-laravel-model-states-actions');
    }
}
