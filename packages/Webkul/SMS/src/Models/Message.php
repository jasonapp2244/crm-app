<?php

namespace Webkul\SMS\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Contact\Models\PersonProxy;
use Webkul\Lead\Models\LeadProxy;
use Webkul\User\Models\UserProxy;
use Webkul\SMS\Contracts\Message as MessageContract;

class Message extends Model implements MessageContract
{
    protected $table = 'sms_messages';

    protected $fillable = [
        'from',
        'to',
        'body',
        'direction',
        'status',
        'channel',
        'twilio_sid',
        'twilio_number_id',
        'person_id',
        'lead_id',
        'user_id',
        'error_message',
        'scheduled_at',
        'template_id',
    ];

    protected $casts = [
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'scheduled_at' => 'datetime',
    ];

    protected $appends = [
        'time_ago',
    ];

    public function getTimeAgoAttribute()
    {
        return $this->created_at?->diffForHumans();
    }

    public function person()
    {
        return $this->belongsTo(PersonProxy::modelClass());
    }

    public function lead()
    {
        return $this->belongsTo(LeadProxy::modelClass());
    }

    public function user()
    {
        return $this->belongsTo(UserProxy::modelClass());
    }

    public function twilioNumber()
    {
        return $this->belongsTo(TwilioNumberProxy::modelClass(), 'twilio_number_id');
    }

    public function template()
    {
        return $this->belongsTo(TemplateProxy::modelClass(), 'template_id');
    }
}
