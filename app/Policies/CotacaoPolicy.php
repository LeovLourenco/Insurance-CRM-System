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
     * - DIRETOR: todas (supervisão hierárquica)
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
     * - DIRETOR: todas (supervisão hierárquica)
     * - COMERCIAL: apenas suas
     */
    public function view(User $user, Cotacao $cotacao)
    {
        // Admin pode ver todas
        if ($user->hasRole('admin') || $user->hasPermissionTo('cotacoes.view.all')) {
            return true;
        }
        
        // Diretor pode ver todas (supervisão hierárquica)
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
     * - ADMIN: sim (qualquer corretora)
     * - DIRETOR: sim (apenas suas corretoras)
     * - COMERCIAL: sim (apenas suas corretoras)
     * 
     * Nota: Ownership é validado no Controller
     */
    public function create(User $user)
    {
        return $user->hasAnyRole(['admin', 'diretor']) || 
               $user->hasPermissionTo('cotacoes.create');
    }

    /**
     * Determine whether the user can update the model.
     * - ADMIN: todas
     * - DIRETOR: apenas que ELE CRIOU (ownership + permissão)
     * - COMERCIAL: apenas que ELE CRIOU (ownership + permissão)
     */
    public function update(User $user, Cotacao $cotacao)
    {
        // Admin pode editar todas
        if ($user->hasRole('admin') || $user->hasPermissionTo('cotacoes.update.all')) {
            return true;
        }
        
        // DIRETOR e COMERCIAL: validação uniforme (ownership + permissão)
        if ($user->hasAnyRole(['diretor', 'comercial'])) {
            return $cotacao->user_id === $user->id && $user->hasPermissionTo('cotacoes.update');
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     * - ADMIN: todas
     * - DIRETOR: apenas que ELE CRIOU (ownership + permissão)
     * - COMERCIAL: apenas que ELE CRIOU (ownership + permissão)
     */
    public function delete(User $user, Cotacao $cotacao)
    {
        // Admin pode deletar todas
        if ($user->hasRole('admin') || $user->hasPermissionTo('cotacoes.delete.all')) {
            return true;
        }
        
        // DIRETOR e COMERCIAL: validação uniforme (ownership + permissão)
        if ($user->hasAnyRole(['diretor', 'comercial'])) {
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