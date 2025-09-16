<?php

namespace App\Policies;

use App\Models\CotacaoSeguradora;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CotacaoSeguradoraPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the model.
     * Baseia-se no acesso à cotação pai
     */
    public function view(User $user, CotacaoSeguradora $cotacaoSeguradora)
    {
        // Admin pode ver todas
        if ($user->hasRole('admin') || $user->hasPermissionTo('cotacao-seguradoras.view.all')) {
            return true;
        }
        
        // Diretor pode ver todas (supervisiona)
        if ($user->hasRole('diretor')) {
            return true;
        }
        
        // Comercial só pode ver se for da sua cotação
        if ($user->hasRole('comercial')) {
            return $cotacaoSeguradora->cotacao->user_id === $user->id;
        }
        
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('cotacao-seguradoras.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CotacaoSeguradora $cotacaoSeguradora)
    {
        // Admin pode editar todas
        if ($user->hasRole('admin') || $user->hasPermissionTo('cotacao-seguradoras.update.all')) {
            return true;
        }
        
        // Diretor não pode editar (apenas supervisiona)
        if ($user->hasRole('diretor')) {
            return false;
        }
        
        // Comercial só pode editar se for da sua cotação
        if ($user->hasRole('comercial')) {
            return $cotacaoSeguradora->cotacao->user_id === $user->id && 
                   $user->hasPermissionTo('cotacao-seguradoras.update');
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CotacaoSeguradora $cotacaoSeguradora)
    {
        // Admin pode deletar todas
        if ($user->hasRole('admin') || $user->hasPermissionTo('cotacao-seguradoras.delete.all')) {
            return true;
        }
        
        // Diretor não pode deletar
        if ($user->hasRole('diretor')) {
            return false;
        }
        
        // Comercial só pode deletar se for da sua cotação
        if ($user->hasRole('comercial')) {
            return $cotacaoSeguradora->cotacao->user_id === $user->id && 
                   $user->hasPermissionTo('cotacao-seguradoras.delete');
        }
        
        return false;
    }

    public function restore(User $user, CotacaoSeguradora $cotacaoSeguradora)
    {
        return $this->delete($user, $cotacaoSeguradora);
    }

    public function forceDelete(User $user, CotacaoSeguradora $cotacaoSeguradora)
    {
        return $user->hasRole('admin');
    }
}