<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cotacao;
use App\Models\Corretora;
use App\Models\Produto;
use App\Models\Seguradora;
use App\Models\Vinculo;
use App\Models\AtividadeCotacao;
use App\Models\Segurado;

class CotacaoController extends Controller
{
    public function index()
{
    $cotacoes = Cotacao::with([
        'corretora',
        'produto',
        'atividades' => function ($query) {
            $query->latest()->with('user'); // ordena atividades da mais recente para a mais antiga
        }
    ])
    ->latest()
    ->get();

    return view('cotacoes.index', compact('cotacoes'));
}


    public function create(Request $request)
{
    $corretoraId = $request->input('corretora_id');
    $produtoId = $request->input('produto_id');
    $seguradoraId = $request->input('seguradora_id');

    $corretoras = Corretora::all();
    $produtos = Produto::all();
    $seguradoras = Seguradora::all();
    $segurados = Segurado::all();

    return view('cotacoes.create', compact(
        'corretoras', 'produtos', 'seguradoras',
        'corretoraId', 'produtoId', 'seguradoraId', 'segurados'
    ));
}



    public function store(Request $request)
{
    $validated = $request->validate([
        'corretora_id' => 'required|exists:corretoras,id',
        'produto_id' => 'required|exists:produtos,id',
        'seguradora_id' => 'required|exists:seguradoras,id',
        'observacoes' => 'nullable|string',
        'segurado_id' => 'required|exists:segurados,id',
        'status' => 'required|string|max255',
    ]);

    // Criar a cotação com os dados validados
    $cotacao = Cotacao::create($validated);

    // Buscar as seguradoras vinculadas à corretora
    $seguradorasDaCorretora = \DB::table('corretora_seguradora')
        ->where('corretora_id', $validated['corretora_id'])
        ->pluck('seguradora_id');

    // Buscar as seguradoras que oferecem o produto
    $seguradorasComProduto = \DB::table('seguradora_produto')
        ->where('produto_id', $validated['produto_id'])
        ->pluck('seguradora_id');

    // Interseção das seguradoras válidas
    $seguradorasIds = $seguradorasDaCorretora->intersect($seguradorasComProduto);

    // Carregar as seguradoras filtradas
    $seguradoras = \App\Models\Seguradora::whereIn('id', $seguradorasIds)->get();

    // Registrar a atividade da criação da cotação associada ao usuário logado
    \App\Models\AtividadeCotacao::create([
        'cotacao_id' => $cotacao->id,
        'descricao' => 'Cotação criada para seguradora(s) por ' . auth()->user()->name,
        'user_id' => auth()->id(),
    ]);

    // Carregar as atividades para exibição na view
    $cotacao->load('atividades');

    // Retornar a view com os dados necessários
    return view('cotacoes.resultado', compact('cotacao', 'seguradoras'));
    
}
}