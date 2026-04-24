<?php

namespace Webkul\SMS\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\SMS\Contracts\Message;

class MessageRepository extends Repository
{
    public function model()
    {
        return Message::class;
    }
}
