<?php

namespace Webkul\SMS\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\SMS\Contracts\Template;

class TemplateRepository extends Repository
{
    public function model()
    {
        return Template::class;
    }
}
