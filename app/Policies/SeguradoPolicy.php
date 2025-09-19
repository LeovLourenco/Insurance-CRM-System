<?php

namespace App\Policies;

use App\Models\Segurado;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SeguradoPolicy
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
    public function view(User $user, Segurado $segurado)
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
     * - ADMIN: sim
     * - DIRETOR: sim
     * - COMERCIAL: não (READ-ONLY)
     */
    public function update(User $user, Segurado $segurado)
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
    public function delete(User $user, Segurado $segurado)
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Segurado $segurado)
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Segurado $segurado)
    {
        return $user->hasRole('admin');
    }
}