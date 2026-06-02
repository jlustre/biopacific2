<?php

namespace App\Services;

use App\Http\Controllers\Concerns\ProvidesMemberPortalContext;

class MemberPortalContextResolver
{
    use ProvidesMemberPortalContext;

    public function forUser($user): array
    {
        if ($user && method_exists($user, 'fresh')) {
            $user = $user->fresh();
        }

        return $this->memberPortalContext($user);
    }
}
