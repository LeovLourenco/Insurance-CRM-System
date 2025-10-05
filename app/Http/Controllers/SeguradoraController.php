<?php

namespace App\Http\Controllers;

use App\Models\Seguradora;
use App\Models\Produto;
use App\Models\Corretora;
use App\Models\CorretoraSeguradora;
use App\Models\SeguradoraProduto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeguradoraController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Seguradora::class, 'seguradora');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Seguradora::query();

        // Filtro por busca
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filtro por seguradoras com produtos
        if ($request->filled('com_produtos') && $request->com_produtos == '1') {
            $query->comProdutos();
        }

        // Filtro por seguradoras com corretoras
        if ($request->filled('com_corretoras') && $request->com_corretoras == '1') {
            $query->comCorretoras();
        }

        // Aplicar filtro de cotações por role
        $user = auth()->user();
        if ($user->hasRole('comercial')) {
            $seguradoras = $query->withCount([
                'produtos',
                'corretoras',
                'cotacoes' => function($q) use ($user) {
                    $q->where('user_id', $user->id);
                }
            ])->latest()->paginate(10);
        } else {
            $seguradoras = $query->withCount(['produtos', 'corretoras', 'cotacoes'])
                                ->latest()
                                ->paginate(10);
        }

        return view('seguradoras.index', compact('seguradoras'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $produtos = Produto::orderBy('nome')->get();
        
        return view('seguradoras.create', compact('produtos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:191|unique:seguradoras,nome',
            'site' => 'nullable|string|max:191|regex:/^(https?:\/\/)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(\/.*)?$/',
            'observacoes' => 'nullable|string|max:2000',
            'produtos' => 'nullable|array',
            'produtos.*' => 'exists:produtos,id'
        ], [
            'nome.required' => 'O nome da seguradora é obrigatório.',
            'nome.unique' => 'Já existe uma seguradora com este nome.',
            'nome.max' => 'O nome deve ter no máximo 191 caracteres.',
            'site.regex' => 'Digite um site válido (ex: www.empresa.com.br).',
            'site.max' => 'O site deve ter no máximo 191 caracteres.',
            'observacoes.max' => 'As observações devem ter no máximo 2000 caracteres.',
            'produtos.*.exists' => 'Um dos produtos selecionados é inválido.'
        ]);

        try {
            DB::beginTransaction();
            
            // 1. Criar a seguradora
            $seguradora = Seguradora::create([
                'nome' => $validated['nome'],
                'site' => $validated['site'],
                'observacoes' => $validated['observacoes']
            ]);
            
            // 2. Vincular produtos se selecionados (com auditoria)
            if (!empty($validated['produtos'])) {
                foreach ($validated['produtos'] as $produtoId) {
                    SeguradoraProduto::firstOrCreate([
                        'seguradora_id' => $seguradora->id,
                        'produto_id' => $produtoId
                    ]);
                }
            }
            
            DB::commit();
            
            return redirect()
                ->route('seguradoras.index')
                ->with('success', 'Seguradora criada com sucesso!');
                
        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao criar seguradora: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Seguradora $seguradora)
    {
        // Carregar produtos (cards - quantidade menor)
        $seguradora->load(['produtos']);
        
        // Carregar corretoras com paginação (tabela - quantidade maior)
        $corretoras = $seguradora->corretoras()
                                ->withPivot('created_at')
                                ->paginate(10, ['*'], 'corretoras');
        
        // Carregar cotações recentes (filtrar por role)
        $user = auth()->user();
        $cotacoesQuery = $seguradora->cotacoes()
                                   ->with(['corretora', 'produto', 'segurado'])
                                   ->latest()
                                   ->limit(10);
        
        if ($user->hasRole('comercial')) {
            $cotacoesQuery->where('user_id', $user->id);
        }
        $cotacoes = $cotacoesQuery->get();

        // Estatísticas de cotações por status (filtrar por role)
        if ($user->hasRole('comercial')) {
            $cotacoesPorStatus = $seguradora->cotacoesPorStatus($user->id);
        } else {
            $cotacoesPorStatus = $seguradora->cotacoesPorStatus();
        }

        return view('seguradoras.show', compact('seguradora', 'corretoras', 'cotacoes', 'cotacoesPorStatus'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Seguradora $seguradora)
    {
        $produtos = Produto::orderBy('nome')->get();
        $corretoras = Corretora::orderBy('nome')->get();
        
        // Carregar relacionamentos atuais
        $seguradora->load(['produtos', 'corretoras']);
        
        return view('seguradoras.edit', compact('seguradora', 'produtos', 'corretoras'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Seguradora $seguradora)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:191|unique:seguradoras,nome,' . $seguradora->id,
            'site' => 'nullable|string|max:191|regex:/^(https?:\/\/)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(\/.*)?$/',
            'observacoes' => 'nullable|string|max:2000',
            'produtos' => 'nullable|array',
            'produtos.*' => 'exists:produtos,id'
        ], [
            'nome.required' => 'O nome da seguradora é obrigatório.',
            'nome.unique' => 'Já existe uma seguradora com este nome.',
            'nome.max' => 'O nome deve ter no máximo 191 caracteres.',
            'site.regex' => 'Digite um site válido (ex: www.empresa.com.br).',
            'site.max' => 'O site deve ter no máximo 191 caracteres.',
            'observacoes.max' => 'As observações devem ter no máximo 2000 caracteres.',
            'produtos.*.exists' => 'Um dos produtos selecionados é inválido.'
        ]);

        try {
            DB::beginTransaction();
            
            // 1. Atualizar a seguradora
            $seguradora->update([
                'nome' => $validated['nome'],
                'site' => $validated['site'],
                'observacoes' => $validated['observacoes']
            ]);
            
            // 2. Atualizar vínculos com produtos (com auditoria)
            $produtos_atuais = $seguradora->produtos->pluck('id')->toArray();
            $produtos_novos = $validated['produtos'] ?? [];
            
            // Remover produtos que não estão mais selecionados
            $produtos_remover = array_diff($produtos_atuais, $produtos_novos);
            foreach ($produtos_remover as $produtoId) {
                SeguradoraProduto::where('seguradora_id', $seguradora->id)
                    ->where('produto_id', $produtoId)
                    ->delete();
            }
            
            // Adicionar novos produtos
            $produtos_adicionar = array_diff($produtos_novos, $produtos_atuais);
            foreach ($produtos_adicionar as $produtoId) {
                SeguradoraProduto::create([
                    'seguradora_id' => $seguradora->id,
                    'produto_id' => $produtoId
                ]);
            }
            
            DB::commit();
            
            return redirect()
                ->route('seguradoras.show', $seguradora)
                ->with('success', 'Seguradora atualizada com sucesso!');
                
        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar seguradora: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Seguradora $seguradora)
    {
        try {
            DB::beginTransaction();
            
            // Verificar se seguradora está sendo usada
            $cotacoesCount = $seguradora->cotacoes()->count();
            
            if ($cotacoesCount > 0) {
                return redirect()
                    ->back()
                    ->with('error', 'Não é possível excluir esta seguradora pois ela possui cotações associadas.');
            }

            // 1. Limpar relacionamentos nas pivots
            SeguradoraProduto::where('seguradora_id', $seguradora->id)->delete();
            CorretoraSeguradora::where('seguradora_id', $seguradora->id)->delete();
            
            // 2. Deletar a seguradora
            $seguradora->delete();
            
            DB::commit();
            
            return redirect()
                ->route('seguradoras.index')
                ->with('success', 'Seguradora excluída com sucesso!');
                
        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()
                ->back()
                ->with('error', 'Erro ao excluir seguradora: ' . $e->getMessage());
        }
    }

    /**
     * Método para busca via AJAX (opcional)
     */
    public function search(Request $request)
    {
        $search = $request->get('q');
        
        $seguradoras = Seguradora::where('nome', 'like', "%{$search}%")
                                ->limit(10)
                                ->get(['id', 'nome', 'site']);

        return response()->json($seguradoras);
    }
}