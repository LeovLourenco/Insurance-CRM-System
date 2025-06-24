<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Segurado;

class SeguradoController extends Controller
{
    public function store(Request $request)
    {
        // Validação dos dados
        $request->validate([
            'nome' => 'required|string|max:255',
            'documento' => 'required|string|max:18', // CPF tem 14 chars com máscara, CNPJ 18
            'telefone' => 'nullable|string|max:20'
        ]);
        try {
            // Criar o segurado
            Segurado::create([
                'nome' => $request->nome,
                'documento' => $request->documento,
                'telefone' => $request->telefone
            ]);

            return redirect()->route('cadastro')->with('success', 'Segurado cadastrado com sucesso!');
            
        } catch (\Exception $e) {
            return redirect()->route('cadastro')->with('error', 'Erro ao cadastrar segurado: ' . $e->getMessage());
        }    
    }
}