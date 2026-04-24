<?php

namespace Webkul\SMS\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\SMS\Contracts\Template as TemplateContract;

class Template extends Model implements TemplateContract
{
    protected $table = 'sms_templates';

    protected $fillable = [
        'name',
        'body',
        'channel',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function messages()
    {
        return $this->hasMany(MessageProxy::modelClass(), 'template_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
