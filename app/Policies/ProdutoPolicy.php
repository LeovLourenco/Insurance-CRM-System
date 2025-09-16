<?php

namespace App\Policies;

use App\Models\Produto;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProdutoPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     * - Todos os usuários autenticados podem listar produtos
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     * - Todos os usuários autenticados podem ver produtos
     */
    public function view(User $user, Produto $produto)
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     * - Apenas admins podem criar produtos
     */
    public function create(User $user)
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the model.
     * - Apenas admins podem editar produtos
     */
    public function update(User $user, Produto $produto)
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the model.
     * - Apenas admins podem deletar produtos
     */
    public function delete(User $user, Produto $produto)
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Produto $produto)
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Produto $produto)
    {
        return $user->hasRole('admin');
    }
}