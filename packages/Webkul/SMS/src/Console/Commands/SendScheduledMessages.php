<?php

namespace Webkul\SMS\Console\Commands;

use Illuminate\Console\Command;
use Webkul\SMS\Services\TwilioService;

class SendScheduledMessages extends Command
{
    protected $signature = 'sms:send-scheduled';

    protected $description = 'Send all scheduled SMS/WhatsApp messages that are due';

    public function handle(TwilioService $twilioService): int
    {
        $sent = $twilioService->processScheduledMessages();

        $this->info("Processed {$sent} scheduled message(s).");

        return self::SUCCESS;
    }
}
