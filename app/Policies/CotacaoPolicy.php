<?php

namespace App\Policies;

use App\Models\Cotacao;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CotacaoPolicy
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
               $user->hasPermissionTo('cotacoes.view');
    }

    /**
     * Determine whether the user can view the model.
     * - ADMIN: todas
     * - DIRETOR: todas (supervisiona) 
     * - COMERCIAL: apenas suas
     */
    public function view(User $user, Cotacao $cotacao)
    {
        // Admin pode ver todas
        if ($user->hasRole('admin') || $user->hasPermissionTo('cotacoes.view.all')) {
            return true;
        }
        
        // Diretor pode ver todas (supervisiona)
        if ($user->hasRole('diretor')) {
            return true;
        }
        
        // Comercial só pode ver suas próprias
        if ($user->hasRole('comercial')) {
            return $cotacao->user_id === $user->id;
        }
        
        return false;
    }

    /**
     * Determine whether the user can create models.
     * - ADMIN: sim
     * - DIRETOR: não (apenas supervisiona)
     * - COMERCIAL: sim
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('cotacoes.create');
    }

    /**
     * Determine whether the user can update the model.
     * - ADMIN: todas
     * - DIRETOR: nenhuma (apenas supervisiona)
     * - COMERCIAL: apenas suas
     */
    public function update(User $user, Cotacao $cotacao)
    {
        // Admin pode editar todas
        if ($user->hasRole('admin') || $user->hasPermissionTo('cotacoes.update.all')) {
            return true;
        }
        
        // Diretor não pode editar (apenas supervisiona)
        if ($user->hasRole('diretor')) {
            return false;
        }
        
        // Comercial só pode editar suas próprias
        if ($user->hasRole('comercial')) {
            return $cotacao->user_id === $user->id && $user->hasPermissionTo('cotacoes.update');
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     * - ADMIN: todas
     * - DIRETOR: nenhuma (apenas supervisiona)
     * - COMERCIAL: apenas suas
     */
    public function delete(User $user, Cotacao $cotacao)
    {
        // Admin pode deletar todas
        if ($user->hasRole('admin') || $user->hasPermissionTo('cotacoes.delete.all')) {
            return true;
        }
        
        // Diretor não pode deletar
        if ($user->hasRole('diretor')) {
            return false;
        }
        
        // Comercial só pode deletar suas próprias
        if ($user->hasRole('comercial')) {
            return $cotacao->user_id === $user->id && $user->hasPermissionTo('cotacoes.delete');
        }
        
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Cotacao $cotacao)
    {
        return $this->delete($user, $cotacao);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Cotacao $cotacao)
    {
        return $user->hasRole('admin');
    }
}