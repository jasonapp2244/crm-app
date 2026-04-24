<?php

namespace Webkul\SMS\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\SMS\Contracts\TwilioNumber;

class TwilioNumberRepository extends Repository
{
    public function model()
    {
        return TwilioNumber::class;
    }
}
