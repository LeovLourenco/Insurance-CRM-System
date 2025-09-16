<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProdutoController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Produto::class, 'produto');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Produto::query();

        // Filtro por busca
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filtro por linha
        if ($request->filled('linha')) {
            $query->where('linha', $request->linha);
        }

        // Aplicar filtro de cotações por role
        $user = auth()->user();
        if ($user->hasRole('comercial')) {
            $produtos = $query->with(['seguradoras'])->withCount([
                'cotacoes' => function($q) use ($user) {
                    $q->where('user_id', $user->id);
                }
            ])->latest()->paginate(10);
        } else {
            $produtos = $query->with(['seguradoras'])->withCount(['cotacoes'])
                             ->latest()->paginate(10);
        }
        
        // Buscar linhas únicas para o filtro
        $linhas = Produto::whereNotNull('linha')
                       ->distinct()
                       ->pluck('linha')
                       ->sort();

        return view('produtos.index', compact('produtos', 'linhas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $seguradoras = \App\Models\Seguradora::orderBy('nome')->get();
        return view('produtos.create', compact('seguradoras'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:191|unique:produtos,nome',
            'linha' => 'nullable|string|max:191',
            'descricao' => 'nullable|string|max:1000',
            'seguradoras' => 'required|array|min:1',
            'seguradoras.*' => 'exists:seguradoras,id'
        ], [
            'nome.required' => 'O nome do produto é obrigatório.',
            'nome.unique' => 'Já existe um produto com este nome.',
            'nome.max' => 'O nome deve ter no máximo 191 caracteres.',
            'linha.max' => 'A linha deve ter no máximo 191 caracteres.',
            'descricao.max' => 'A descrição deve ter no máximo 1000 caracteres.',
            'seguradoras.required' => 'Selecione pelo menos uma seguradora.',
            'seguradoras.min' => 'Selecione pelo menos uma seguradora.',
            'seguradoras.*.exists' => 'Uma das seguradoras selecionadas é inválida.'
        ]);

        try {
            DB::beginTransaction();
            
            // 1. Criar o produto
            $produto = Produto::create([
                'nome' => $validated['nome'],
                'linha' => $validated['linha'],
                'descricao' => $validated['descricao']
            ]);
            
            // 2. Vincular seguradoras (tabela pivot)
            $produto->seguradoras()->sync($validated['seguradoras']);
            
            DB::commit();
            
            return redirect()
                ->route('produtos.index')
                ->with('success', 'Produto criado com sucesso e vinculado às seguradoras selecionadas!');
                
        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao criar produto: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Produto $produto)
    {
        // Carregar relacionamentos para mostrar detalhes (filtrar por role)
        $user = auth()->user();
        
        $cotacoesQuery = function($query) use ($user) {
            $query->with(['corretora', 'segurado', 'cotacaoSeguradoras.seguradora'])
                ->latest()
                ->limit(5);
            
            if ($user->hasRole('comercial')) {
                $query->where('user_id', $user->id);
            }
        };
        
        $produto->load(['seguradoras', 'cotacoes' => $cotacoesQuery]);

        return view('produtos.show', compact('produto'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Produto $produto)
    {
        $seguradoras = \App\Models\Seguradora::orderBy('nome')->get();
        
        // Carregar seguradoras já vinculadas
        $produto->load('seguradoras');
        
        return view('produtos.edit', compact('produto', 'seguradoras'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Produto $produto)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:191|unique:produtos,nome,' . $produto->id,
            'linha' => 'nullable|string|max:191',
            'descricao' => 'nullable|string|max:1000',
            'seguradoras' => 'required|array|min:1',
            'seguradoras.*' => 'exists:seguradoras,id'
        ], [
            'nome.required' => 'O nome do produto é obrigatório.',
            'nome.unique' => 'Já existe um produto com este nome.',
            'nome.max' => 'O nome deve ter no máximo 191 caracteres.',
            'linha.max' => 'A linha deve ter no máximo 191 caracteres.',
            'descricao.max' => 'A descrição deve ter no máximo 1000 caracteres.',
            'seguradoras.required' => 'Selecione pelo menos uma seguradora.',
            'seguradoras.min' => 'Selecione pelo menos uma seguradora.',
            'seguradoras.*.exists' => 'Uma das seguradoras selecionadas é inválida.'
        ]);

        try {
            DB::beginTransaction();
            
            // 1. Atualizar o produto
            $produto->update([
                'nome' => $validated['nome'],
                'linha' => $validated['linha'],
                'descricao' => $validated['descricao']
            ]);
            
            // 2. Atualizar vínculos com seguradoras (sync remove antigas e adiciona novas)
            $produto->seguradoras()->sync($validated['seguradoras']);
            
            DB::commit();
            
            return redirect()
                ->route('produtos.show', $produto)
                ->with('success', 'Produto atualizado com sucesso!');
                
        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar produto: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Produto $produto)
    {
        try {
            // Verificar se produto está sendo usado
            $cotacoesCount = $produto->cotacoes()->count();
            
            if ($cotacoesCount > 0) {
                return redirect()
                    ->back()
                    ->with('error', 'Não é possível excluir este produto pois ele possui cotações associadas.');
            }

            $produto->delete();
            
            return redirect()
                ->route('produtos.index')
                ->with('success', 'Produto excluído com sucesso!');
                
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erro ao excluir produto: ' . $e->getMessage());
        }
    }

    /**
     * Método para busca via AJAX (opcional)
     */
    public function search(Request $request)
    {
        $search = $request->get('q');
        
        $produtos = Produto::where('nome', 'like', "%{$search}%")
                          ->orWhere('tipo', 'like', "%{$search}%")
                          ->limit(10)
                          ->get(['id', 'nome', 'tipo']);

        return response()->json($produtos);
    }
}