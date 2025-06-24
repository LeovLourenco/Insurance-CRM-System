<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Corretora;

class CorretoraController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'seguradoras' => 'nullable|array',
            'seguradoras.*' => 'exists:seguradoras,id'
        ]);

        try {
            // Criar a corretora
            $corretora = Corretora::create([
                'nome' => $request->nome,
            ]);

            // Se foram selecionadas seguradoras, vincular
            if ($request->has('seguradoras') && !empty($request->seguradoras)) {
                $corretora->seguradoras()->attach($request->seguradoras);
            }

            return redirect()->route('cadastro')->with('success', 'Corretora cadastrada com sucesso!');
            
        } catch (\Exception $e) {
            return redirect()->route('cadastro')->with('error', 'Erro ao cadastrar corretora: ' . $e->getMessage());
        }
    }
}