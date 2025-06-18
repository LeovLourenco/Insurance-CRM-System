<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Corretora;
use App\Models\Seguradora;
use App\Models\Produto;
use App\Models\Vinculo;

class VinculoController extends Controller
{
    public function index()
        {
            $corretoras = Corretora::all();
            $seguradoras = Seguradora::all();
            $produtos = Produto::all();
            $vinculos = Vinculo::with(['corretora', 'seguradora', 'produto'])->get();

            return view('vinculos.index', compact('corretoras', 'seguradoras', 'produtos', 'vinculos'));
        }

        public function store(Request $request)
        {
            $request->validate([
                'corretora_id' => 'required|exists:corretoras,id',
                'seguradora_id' => 'required|exists:seguradoras,id',
                'produto_id' => 'required|exists:produtos,id',
            ]);

            Vinculo::firstOrCreate([
                'corretora_id' => $request->corretora_id,
                'seguradora_id' => $request->seguradora_id,
                'produto_id' => $request->produto_id,
            ], [
                'canal' => $request->canal,
                'observacoes' => $request->observacoes,
            ]);

            return redirect()->route('vinculos.index')->with('success', 'Vínculo cadastrado com sucesso!');
        }
    }
