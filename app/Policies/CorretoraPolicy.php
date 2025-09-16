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
     * - ADMIN: todas
     * - DIRETOR: todas (supervisiona)
     * - COMERCIAL: apenas suas
     */
    public function viewAny(User $user)
    {
        return $user->hasAnyRole(['admin', 'diretor']) || 
               $user->hasPermissionTo('corretoras.view');
    }

    /**
     * Determine whether the user can view the model.
     * - ADMIN: todas
     * - DIRETOR: todas (supervisiona)
     * - COMERCIAL: apenas as que são responsáveis
     */
    public function view(User $user, Corretora $corretora)
    {
        // Admin pode ver todas
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // Diretor pode ver todas (supervisiona)
        if ($user->hasRole('diretor')) {
            return true;
        }
        
        // Comercial só pode ver corretoras que são responsáveis
        if ($user->hasRole('comercial')) {
            return $corretora->usuario_id === $user->id;
        }
        
        return false;
    }

    /**
     * Determine whether the user can create models.
     * - ADMIN: sim
     * - DIRETOR: sim
     * - COMERCIAL: não (apenas admins/diretores podem criar corretoras)
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('corretoras.create');
    }

    /**
     * Determine whether the user can update the model.
     * - ADMIN: todas
     * - DIRETOR: todas
     * - COMERCIAL: apenas as que são responsáveis
     */
    public function update(User $user, Corretora $corretora)
    {
        // Admin pode editar todas
        if ($user->hasRole('admin') || $user->hasPermissionTo('corretoras.update.all')) {
            return true;
        }
        
        // Diretor pode editar todas
        if ($user->hasRole('diretor')) {
            return true;
        }
        
        // Comercial só pode editar corretoras que são responsáveis
        if ($user->hasRole('comercial')) {
            return $corretora->usuario_id === $user->id && $user->hasPermissionTo('corretoras.update');
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     * - ADMIN: todas
     * - DIRETOR: nenhuma (apenas supervisiona)
     * - COMERCIAL: nenhuma
     */
    public function delete(User $user, Corretora $corretora)
    {
        // Apenas admin pode deletar corretoras
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
