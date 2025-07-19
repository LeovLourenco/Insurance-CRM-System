<?php

namespace App\Http\Controllers;

use App\Models\Corretora;
use App\Models\Seguradora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CorretoraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Corretora::query();

        // Filtro por busca
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filtro por corretoras com seguradoras
        if ($request->filled('com_seguradoras') && $request->com_seguradoras == '1') {
            $query->comSeguradoras();
        }

        // Filtro por corretoras com cotações
        if ($request->filled('com_cotacoes') && $request->com_cotacoes == '1') {
            $query->comCotacoes();
        }

        $corretoras = $query->withCount(['seguradoras', 'cotacoes'])
                           ->latest()
                           ->paginate(10);

        return view('corretoras.index', compact('corretoras'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $seguradoras = Seguradora::orderBy('nome')->get();
        
        return view('corretoras.create', compact('seguradoras'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:191|unique:corretoras,nome',
            'email' => 'nullable|email|max:191|unique:corretoras,email',
            'telefone' => 'nullable|string|max:20',
            'seguradoras' => 'nullable|array',
            'seguradoras.*' => 'exists:seguradoras,id'
        ], [
            'nome.required' => 'O nome da corretora é obrigatório.',
            'nome.unique' => 'Já existe uma corretora com este nome.',
            'nome.max' => 'O nome deve ter no máximo 191 caracteres.',
            'email.email' => 'Digite um email válido.',
            'email.unique' => 'Este email já está sendo usado por outra corretora.',
            'email.max' => 'O email deve ter no máximo 191 caracteres.',
            'telefone.max' => 'O telefone deve ter no máximo 20 caracteres.',
            'seguradoras.*.exists' => 'Uma das seguradoras selecionadas é inválida.'
        ]);

        try {
            DB::beginTransaction();
            
            // 1. Criar a corretora
            $corretora = Corretora::create([
                'nome' => $validated['nome'],
                'email' => $validated['email'],
                'telefone' => $validated['telefone']
            ]);
            
            // 2. Vincular seguradoras se selecionadas
            if (!empty($validated['seguradoras'])) {
                $corretora->seguradoras()->sync($validated['seguradoras']);
            }
            
            DB::commit();
            
            return redirect()
                ->route('corretoras.index')
                ->with('success', 'Corretora criada com sucesso!');
                
        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao criar corretora: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Corretora $corretora)
    {
        // Carregar seguradoras com paginação (tabela - quantidade maior)
        $seguradoras = $corretora->seguradoras()
                                ->withPivot('created_at')
                                ->paginate(10, ['*'], 'seguradoras');
        
        // Carregar cotações recentes
        $cotacoes = $corretora->cotacoes()
                             ->with(['seguradora', 'produto', 'segurado'])
                             ->latest()
                             ->limit(10)
                             ->get();

        // Estatísticas de cotações por status
        $cotacoesPorStatus = $corretora->cotacoesPorStatus();

        return view('corretoras.show', compact('corretora', 'seguradoras', 'cotacoes', 'cotacoesPorStatus'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Corretora $corretora)
    {
        $seguradoras = Seguradora::orderBy('nome')->get();
        
        // Carregar relacionamentos atuais
        $corretora->load(['seguradoras']);
        
        return view('corretoras.edit', compact('corretora', 'seguradoras'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Corretora $corretora)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:191|unique:corretoras,nome,' . $corretora->id,
            'email' => 'nullable|email|max:191|unique:corretoras,email,' . $corretora->id,
            'telefone' => 'nullable|string|max:20',
            'seguradoras' => 'nullable|array',
            'seguradoras.*' => 'exists:seguradoras,id'
        ], [
            'nome.required' => 'O nome da corretora é obrigatório.',
            'nome.unique' => 'Já existe uma corretora com este nome.',
            'nome.max' => 'O nome deve ter no máximo 191 caracteres.',
            'email.email' => 'Digite um email válido.',
            'email.unique' => 'Este email já está sendo usado por outra corretora.',
            'email.max' => 'O email deve ter no máximo 191 caracteres.',
            'telefone.max' => 'O telefone deve ter no máximo 20 caracteres.',
            'seguradoras.*.exists' => 'Uma das seguradoras selecionadas é inválida.'
        ]);

        try {
            DB::beginTransaction();
            
            // 1. Atualizar a corretora
            $corretora->update([
                'nome' => $validated['nome'],
                'email' => $validated['email'],
                'telefone' => $validated['telefone']
            ]);
            
            // 2. Atualizar vínculos com seguradoras
            $corretora->seguradoras()->sync($validated['seguradoras'] ?? []);
            
            DB::commit();
            
            return redirect()
                ->route('corretoras.show', $corretora)
                ->with('success', 'Corretora atualizada com sucesso!');
                
        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar corretora: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Corretora $corretora)
    {
        try {
            DB::beginTransaction();
            
            // Verificar se corretora está sendo usada
            $cotacoesCount = $corretora->cotacoes()->count();
            $vinculosCount = $corretora->vinculos()->count();
            
            if ($cotacoesCount > 0 || $vinculosCount > 0) {
                return redirect()
                    ->back()
                    ->with('error', 'Não é possível excluir esta corretora pois ela possui cotações ou vínculos associados.');
            }

            // 1. Limpar relacionamentos nas pivots
            $corretora->seguradoras()->detach();
            
            // 2. Deletar a corretora
            $corretora->delete();
            
            DB::commit();
            
            return redirect()
                ->route('corretoras.index')
                ->with('success', 'Corretora excluída com sucesso!');
                
        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()
                ->back()
                ->with('error', 'Erro ao excluir corretora: ' . $e->getMessage());
        }
    }

    /**
     * Método para busca via AJAX (opcional)
     */
    public function search(Request $request)
    {
        $search = $request->get('q');
        
        $corretoras = Corretora::where('nome', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%")
                              ->limit(10)
                              ->get(['id', 'nome', 'email', 'telefone']);

        return response()->json($corretoras);
    }
}