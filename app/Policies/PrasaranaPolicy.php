<?php

namespace App\Policies;

use App\Models\Prasarana;
use App\Models\User;

class PrasaranaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('sarpras.view');
    }

    public function view(User $user, Prasarana $prasarana): bool
    {
        return $user->can('sarpras.view');
    }

    public function create(User $user): bool
    {
        return $user->can('sarpras.create');
    }

    public function update(User $user, Prasarana $prasarana): bool
    {
        return $user->can('sarpras.edit');
    }

    public function delete(User $user, Prasarana $prasarana): bool
    {
        return $user->can('sarpras.delete');
    }

    public function updateStatus(User $user, Prasarana $prasarana): bool
    {
        return $user->can('sarpras.status_update');
    }
}



