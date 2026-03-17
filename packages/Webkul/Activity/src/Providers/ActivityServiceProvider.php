<?php

namespace Webkul\Activity\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Webkul\Activity\Contracts\Activity as ActivityContract;
use Webkul\Activity\Contracts\File as FileContract;
use Webkul\Activity\Contracts\Participant as ParticipantContract;
use Webkul\Activity\Models\Activity;
use Webkul\Activity\Models\File;
use Webkul\Activity\Models\Participant;

class ActivityServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Fallback bindings ensure repositories can resolve contracts in all environments.
        $this->app->bindIf(ActivityContract::class, Activity::class);
        $this->app->bindIf(FileContract::class, File::class);
        $this->app->bindIf(ParticipantContract::class, Participant::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }
}
