<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cotacao;
use App\Models\Corretora;
use App\Models\Produto;
use App\Models\Seguradora;
use App\Models\Vinculo;

class CotacaoController extends Controller
{
    public function index()
    {
        $cotacoes = Cotacao::with(['corretora', 'produto'])->latest()->get();
        return view('cotacoes.index', compact('cotacoes'));
    }

    public function create()
    {
        $corretoras = \App\Models\Corretora::all();
        return view('cotacoes.create', compact('corretoras'));
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'corretora_id' => 'required|exists:corretoras,id',
        'produto' => 'required|string|max:255',
    ]);

    $cotacao = \App\Models\Cotacao::create($validated);

    // Por enquanto, mostra todas as seguradoras (vamos filtrar depois)
    $seguradorasDisponiveis = \App\Models\Seguradora::all();

    return view('cotacoes.show_seguradoras', compact('cotacao', 'seguradorasDisponiveis'));
}


}
