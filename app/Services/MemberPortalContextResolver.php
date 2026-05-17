<?php

namespace App\Services;

use App\Http\Controllers\Concerns\ProvidesMemberPortalContext;

class MemberPortalContextResolver
{
    use ProvidesMemberPortalContext;

    public function forUser($user): array
    {
        return $this->memberPortalContext($user);
    }
}
