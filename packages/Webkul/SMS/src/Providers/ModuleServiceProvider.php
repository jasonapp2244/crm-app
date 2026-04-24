<?php

namespace Webkul\SMS\Providers;

use Webkul\Core\Providers\BaseModuleServiceProvider;
use Webkul\SMS\Models\Message;
use Webkul\SMS\Models\Template;
use Webkul\SMS\Models\TwilioNumber;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        Message::class,
        Template::class,
        TwilioNumber::class,
    ];

    public function boot(): void
    {
        parent::boot();

        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }
}
