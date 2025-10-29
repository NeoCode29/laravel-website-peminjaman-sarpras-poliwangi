<?php

namespace App\Policies;

use App\Models\Marking;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MarkingPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('peminjaman.view') || 
               $user->hasPermissionTo('peminjaman.create');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Marking  $marking
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Marking $marking)
    {
        // User can view their own marking
        if ($marking->user_id === $user->id) {
            return true;
        }

        // Admin or user with view permission can view any marking
        return $user->hasPermissionTo('peminjaman.view');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('peminjaman.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Marking  $marking
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Marking $marking)
    {
        // User can update their own marking if it's active
        if ($marking->user_id === $user->id && $marking->isActive()) {
            return true;
        }

        // User with marking_override permission can update any marking
        return $user->hasPermissionTo('peminjaman.marking_override');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Marking  $marking
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Marking $marking)
    {
        // User can delete their own marking if it's active
        if ($marking->user_id === $user->id && $marking->isActive()) {
            return true;
        }

        // User with marking_override permission can delete any marking
        return $user->hasPermissionTo('peminjaman.marking_override');
    }

    /**
     * Determine whether the user can extend the marking.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Marking  $marking
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function extend(User $user, Marking $marking)
    {
        // User can extend their own marking if it's active
        if ($marking->user_id === $user->id && $marking->isActive()) {
            return true;
        }

        // User with marking_override permission can extend any marking
        return $user->hasPermissionTo('peminjaman.marking_override');
    }

    /**
     * Determine whether the user can convert the marking to peminjaman.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Marking  $marking
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function convertToPeminjaman(User $user, Marking $marking)
    {
        // User can convert their own marking if it's active and not expired
        if ($marking->user_id === $user->id && $marking->canBeConverted()) {
            return true;
        }

        // User with marking_override permission can convert any marking
        return $user->hasPermissionTo('peminjaman.marking_override');
    }
}


