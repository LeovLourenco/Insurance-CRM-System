<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Corretora;
use App\Models\Produto;
use App\Models\Seguradora;

class ConsultaController extends Controller
{
    public function index()
    {
        $corretoras = Corretora::orderBy('nome')->get();
        $produtos = Produto::orderBy('nome')->get();
        return view('consultas.seguros', compact('corretoras', 'produtos'));
    }

    public function buscar(Request $request)
    {
        $corretoraId = $request->input('corretora_id');
        $produtoId = $request->input('produto_id');

        $corretora = Corretora::find($corretoraId);
        $produto = Produto::find($produtoId);

        
        $seguradoras = Seguradora::whereHas('corretoras', function ($query) use ($corretoraId) {
            $query->where('corretoras.id', $corretoraId);
        })->whereHas('produtos', function ($query) use ($produtoId) {
            $query->where('produtos.id', $produtoId);
        })->get();

        $corretoras = Corretora::orderBy('nome')->get();
        $produtos = Produto::orderBy('nome')->get();

        return view('consultas.seguros', compact('corretoras', 'produtos', 'seguradoras', 'corretora', 'produto'));
    }
}
