<?php

namespace App\Policies;

use App\Models\Seguradora;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SeguradoraPolicy
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
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Seguradora  $seguradora
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Seguradora $seguradora)
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     * 🔒 ENTIDADES BASE: Apenas admin/diretor podem criar
     * - ADMIN: sim
     * - DIRETOR: sim
     * - COMERCIAL: não (READ-ONLY)
     */
    public function create(User $user)
    {
        return $user->hasAnyRole(['admin', 'diretor']);
    }

    /**
     * Determine whether the user can update the model.
     * 🔒 ENTIDADES BASE: Apenas admin/diretor podem editar
     * - ADMIN: sim
     * - DIRETOR: sim
     * - COMERCIAL: não (READ-ONLY)
     */
    public function update(User $user, Seguradora $seguradora)
    {
        return $user->hasAnyRole(['admin', 'diretor']);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Seguradora  $seguradora
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Seguradora $seguradora)
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Seguradora  $seguradora
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Seguradora $seguradora)
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Seguradora  $seguradora
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Seguradora $seguradora)
    {
        return $user->hasRole('admin');
    }
}
