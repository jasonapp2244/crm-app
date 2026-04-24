<?php

namespace Webkul\SMS\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\SMS\Contracts\TwilioNumber as TwilioNumberContract;

class TwilioNumber extends Model implements TwilioNumberContract
{
    protected $table = 'twilio_numbers';

    protected $fillable = [
        'label',
        'phone_number',
        'twilio_sid',
        'twilio_token',
        'is_whatsapp',
        'is_active',
    ];

    protected $casts = [
        'is_whatsapp' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function messages()
    {
        return $this->hasMany(MessageProxy::modelClass(), 'twilio_number_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
