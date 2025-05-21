<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configurateModels();
        $this->configurateCommands();
        $this->configurateURL();
    }

    private function configurateModels(): void
    {
        Model::automaticallyEagerLoadRelationships();
        Model::unguard();
        Model::shouldBeStrict(! app()->isProduction());
        Model::preventLazyLoading(! app()->isProduction());
    }

    private function configurateCommands(): void
    {
        DB::prohibitDestructiveCommands(
            app()->isProduction()
        );
    }

    private function configurateURL(): void
    {
        if (app()->isProduction()) {
            URL::forceScheme('https');
        }
    }
}
