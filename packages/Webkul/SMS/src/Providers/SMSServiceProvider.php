<?php

namespace Webkul\SMS\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Webkul\SMS\Console\Commands\SendScheduledMessages;

class SMSServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                SendScheduledMessages::class,
            ]);
        }

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('sms:send-scheduled')->everyMinute();
        });
    }

    public function register(): void
    {
        //
    }
}
