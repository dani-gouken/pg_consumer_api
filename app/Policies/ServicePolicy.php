<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;

class ServicePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {

    }

    public function viewProducts(?User $user, Service $service)
    {
        return $service->enabled && $service->public;
    }
}