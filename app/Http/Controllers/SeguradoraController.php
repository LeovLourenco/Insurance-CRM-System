<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Seguradora;

class SeguradoraController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'produtos' => 'nullable|array',
            'produtos.*' => 'exists:produtos,id'
        ]);

        try {
            // Criar a seguradora
            $seguradora = Seguradora::create([
                'nome' => $request->nome,
            ]);

            // Se foram selecionados produtos, vincular
            if ($request->has('produtos') && !empty($request->produtos)) {
                $seguradora->produtos()->attach($request->produtos);
            }

            return redirect()->route('cadastro')->with('success', 'Seguradora cadastrada com sucesso!');
            
        } catch (\Exception $e) {
            return redirect()->route('cadastro')->with('error', 'Erro ao cadastrar seguradora: ' . $e->getMessage());
        }
    }
}