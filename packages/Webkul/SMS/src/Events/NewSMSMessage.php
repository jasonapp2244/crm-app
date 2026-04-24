<?php

namespace Webkul\SMS\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Webkul\SMS\Models\Message;

class NewSMSMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $messageData;

    public function __construct(public Message $message)
    {
        $this->messageData = [
            'id' => $message->id,
            'from' => $message->from,
            'to' => $message->to,
            'body' => $message->body,
            'direction' => $message->direction,
            'status' => $message->status,
            'channel' => $message->channel,
            'person_id' => $message->person_id,
            'user_id' => $message->user_id,
            'created_at' => $message->created_at?->format('M d, h:i A'),
            'twilio_number_label' => $message->twilioNumber?->label,
        ];
    }

    public function broadcastOn(): array
    {
        $channels = [
            new Channel('sms-messages'),
        ];

        if ($this->message->person_id) {
            $channels[] = new Channel('sms-conversation.'.$this->message->person_id);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'new.sms.message';
    }
}
