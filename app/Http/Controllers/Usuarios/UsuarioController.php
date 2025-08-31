<?php
namespace App\Http\Controllers\Usuarios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'telefone' => 'nullable|string|max:20',
            'endereco' => 'nullable|string|max:255',
            'cep' => 'nullable|string|max:10',
        ]);

        $user->update([
            'name' => $request->nome,
            'email' => $request->email,
            'telefone' => $request->telefone,
            'endereco' => $request->endereco,
            'cep' => $request->cep,
        ]);

        return redirect()->route('usuario.perfil')->with('success', 'Dados atualizados com sucesso!');
    }

    public function alterarSenha(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Verificar se a senha atual está correta
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->withErrors(['current_password' => 'A senha atual está incorreta.']);
        }

        // Atualizar a senha
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('usuario.perfil')->with('success', 'Senha alterada com sucesso!');
    }
}
