<?php

namespace App\Policies;

use App\Models\Corretora;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CorretoraPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     * ✅ ENTIDADES BASE: Todos veem todas (arquitetura correta)
     * - ADMIN: todas
     * - DIRETOR: todas
     * - COMERCIAL: todas (READ-ONLY)
     */
    public function viewAny(User $user)
    {
        return true; // Entidades base são compartilhadas
    }

    /**
     * Determine whether the user can view the model.
     * ✅ ENTIDADES BASE: Todos veem todas (arquitetura correta)
     * - ADMIN: todas
     * - DIRETOR: todas
     * - COMERCIAL: todas (READ-ONLY)
     */
    public function view(User $user, Corretora $corretora)
    {
        return true; // Entidades base são compartilhadas
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
     * - ADMIN: todas
     * - DIRETOR: todas
     * - COMERCIAL: não (READ-ONLY)
     */
    public function update(User $user, Corretora $corretora)
    {
        return $user->hasAnyRole(['admin', 'diretor']);
    }

    /**
     * Determine whether the user can delete the model.
     * 🔒 ENTIDADES BASE: Apenas admin pode deletar
     * - ADMIN: sim
     * - DIRETOR: não (apenas supervisiona)
     * - COMERCIAL: não (READ-ONLY)
     */
    public function delete(User $user, Corretora $corretora)
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Corretora  $corretora
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Corretora $corretora)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Corretora  $corretora
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Corretora $corretora)
    {
        //
    }
}
