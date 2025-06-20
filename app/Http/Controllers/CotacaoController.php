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
    $produtos = \App\Models\Produto::all();
    return view('cotacoes.create', compact('corretoras', 'produtos'));
}


    public function store(Request $request)
{
    $validated = $request->validate([
        'corretora_id' => 'required|exists:corretoras,id',
        'produto_id' => 'required|exists:produtos,id',
        'observacoes' => 'nullable|string',
    ]);

    $cotacao = Cotacao::create($validated);

    // Buscar as seguradoras que têm vínculo com a corretora
    $seguradorasDaCorretora = \DB::table('corretora_seguradora')
        ->where('corretora_id', $validated['corretora_id'])
        ->pluck('seguradora_id');

    // Buscar as seguradoras que oferecem o produto
    $seguradorasComProduto = \DB::table('seguradora_produto')
        ->where('produto_id', $validated['produto_id'])
        ->pluck('seguradora_id');

    // Interseção entre as duas listas
    $seguradorasIds = $seguradorasDaCorretora->intersect($seguradorasComProduto);

    // Carrega os dados das seguradoras filtradas
    $seguradoras = \App\Models\Seguradora::whereIn('id', $seguradorasIds)->get();

    return view('cotacoes.resultado', compact('cotacao', 'seguradoras'));
}





}
