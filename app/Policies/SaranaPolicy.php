<?php

namespace App\Policies;

use App\Models\Sarana;
use App\Models\User;

class SaranaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('sarpras.view');
    }

    public function view(User $user, Sarana $sarana): bool
    {
        return $user->can('sarpras.view');
    }

    public function create(User $user): bool
    {
        return $user->can('sarpras.create');
    }

    public function update(User $user, Sarana $sarana): bool
    {
        return $user->can('sarpras.edit');
    }

    public function delete(User $user, Sarana $sarana): bool
    {
        return $user->can('sarpras.delete');
    }

    public function unitManage(User $user, Sarana $sarana): bool
    {
        return $user->can('sarpras.unit_manage') && $sarana->type === 'serialized';
    }
}



