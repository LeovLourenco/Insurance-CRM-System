<?php
namespace App\Http\Controllers\Usuarios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    public function perfil()
    {
        $usuario = auth()->user();
        return view('usuario.perfil', compact('usuario'));
    }

    public function atualizar(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:usuarios,email,' . $user->id,
        ]);

        $user->update([
            'nome' => $request->nome,
            'email' => $request->email,
        ]);

        return redirect()->route('usuario.perfil')->with('success', 'Dados atualizados com sucesso!');
    }
}
