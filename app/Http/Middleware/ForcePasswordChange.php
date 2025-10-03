<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Se o usuário está autenticado e ainda não alterou a senha padrão
        if (auth()->check() && !auth()->user()->password_changed) {
            // Permitir apenas acesso às rotas de perfil, logout e alteração de senha
            $allowedRoutes = [
                'usuario.perfil',
                'usuario.atualizar', 
                'usuario.alterar.senha',
                'logout'
            ];
            
            if (!in_array($request->route()->getName(), $allowedRoutes)) {
                return redirect()->route('usuario.perfil')
                    ->with('warning', 'Você deve alterar sua senha padrão antes de continuar.');
            }
        }
        
        return $next($request);
    }
}
