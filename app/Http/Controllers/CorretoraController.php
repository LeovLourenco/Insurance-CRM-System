<?php

namespace App\Http\Controllers;

use App\Models\Corretora;
use App\Models\Seguradora;
use App\Models\CorretoraSeguradora;
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
        $user = auth()->user();

        // ✅ ENTIDADES BASE: Todos veem todas (arquitetura correta)
        // Comerciais podem ver todas as corretoras mas policies controlam ações

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

        // ✅ CORE OPERACIONAL: Contar cotações isoladas por comercial
        if ($user->hasRole('comercial')) {
            $corretoras = $query->withCount([
                'seguradoras',
                'cotacoes' => function($q) use ($user) {
                    $q->where('user_id', $user->id);
                }
            ])->latest()->paginate(10);
        } else {
            $corretoras = $query->withCount(['seguradoras', 'cotacoes'])
                               ->latest()
                               ->paginate(10);
        }

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
            'email' => 'nullable|email|max:500|unique:corretoras,email',
            'telefone' => 'nullable|string|max:20',
            'suc_cpd' => 'nullable|string|max:191',
            'estado' => 'nullable|string|max:191',
            'cidade' => 'nullable|string|max:191',
            'cpf_cnpj' => 'nullable|string|max:191',
            'susep' => 'nullable|string|max:191',
            'email2' => 'nullable|string',
            'email3' => 'nullable|string',
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
            'suc_cpd.max' => 'O SUC-CPD deve ter no máximo 191 caracteres.',
            'estado.max' => 'O estado deve ter no máximo 191 caracteres.',
            'cidade.max' => 'A cidade deve ter no máximo 191 caracteres.',
            'cpf_cnpj.max' => 'O CPF/CNPJ deve ter no máximo 191 caracteres.',
            'susep.max' => 'O SUSEP deve ter no máximo 191 caracteres.',
            'seguradoras.*.exists' => 'Uma das seguradoras selecionadas é inválida.'
        ]);

        try {
            DB::beginTransaction();
            
            // 1. Criar a corretora
            $corretora = Corretora::create([
                'nome' => $validated['nome'],
                'email' => $validated['email'],
                'telefone' => $validated['telefone'],
                'suc_cpd' => $validated['suc_cpd'],
                'estado' => $validated['estado'],
                'cidade' => $validated['cidade'],
                'cpf_cnpj' => $validated['cpf_cnpj'],
                'susep' => $validated['susep'],
                'email2' => $validated['email2'],
                'email3' => $validated['email3']
            ]);
            
            // 2. Vincular seguradoras se selecionadas (com auditoria)
            if (!empty($validated['seguradoras'])) {
                foreach ($validated['seguradoras'] as $seguradoraId) {
                    CorretoraSeguradora::firstOrCreate([
                        'corretora_id' => $corretora->id,
                        'seguradora_id' => $seguradoraId
                    ]);
                }
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
        // Carregar relacionamento do usuário responsável
        $corretora->load('usuario');
        
        // Carregar seguradoras com paginação (tabela - quantidade maior)
        $seguradoras = $corretora->seguradoras()
                                ->withPivot('created_at')
                                ->paginate(10, ['*'], 'seguradoras');
        
        // Carregar cotações recentes (filtrar por role)
        $user = auth()->user();
        $cotacoesQuery = $corretora->cotacoes()
                                  ->with(['produto', 'segurado', 'cotacaoSeguradoras.seguradora'])
                                  ->latest()
                                  ->limit(10);
        
        if ($user->hasRole('comercial')) {
            $cotacoesQuery->where('user_id', $user->id);
        }
        $cotacoes = $cotacoesQuery->get();

        // Estatísticas de cotações por status (filtrar por role)
        if ($user->hasRole('comercial')) {
            $cotacoesPorStatus = $corretora->cotacoesPorStatus($user->id);
        } else {
            $cotacoesPorStatus = $corretora->cotacoesPorStatus();
        }

        // Carregar seguradoras disponíveis (não vinculadas)
        $seguradoras_disponiveis = \App\Models\Seguradora::whereNotIn('id', $corretora->seguradoras->pluck('id'))
                                                          ->orderBy('nome')
                                                          ->get();

        return view('corretoras.show', compact('corretora', 'seguradoras', 'cotacoes', 'cotacoesPorStatus', 'seguradoras_disponiveis'));
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
            'email' => 'nullable|email|max:500|unique:corretoras,email,' . $corretora->id,
            'telefone' => 'nullable|string|max:20',
            'suc_cpd' => 'nullable|string|max:191',
            'estado' => 'nullable|string|max:191',
            'cidade' => 'nullable|string|max:191',
            'cpf_cnpj' => 'nullable|string|max:191',
            'susep' => 'nullable|string|max:191',
            'email2' => 'nullable|string',
            'email3' => 'nullable|string',
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
            'suc_cpd.max' => 'O SUC-CPD deve ter no máximo 191 caracteres.',
            'estado.max' => 'O estado deve ter no máximo 191 caracteres.',
            'cidade.max' => 'A cidade deve ter no máximo 191 caracteres.',
            'cpf_cnpj.max' => 'O CPF/CNPJ deve ter no máximo 191 caracteres.',
            'susep.max' => 'O SUSEP deve ter no máximo 191 caracteres.',
            'seguradoras.*.exists' => 'Uma das seguradoras selecionadas é inválida.'
        ]);

        try {
            DB::beginTransaction();
            
            // 1. Atualizar a corretora
            $corretora->update([
                'nome' => $validated['nome'],
                'email' => $validated['email'],
                'telefone' => $validated['telefone'],
                'suc_cpd' => $validated['suc_cpd'],
                'estado' => $validated['estado'],
                'cidade' => $validated['cidade'],
                'cpf_cnpj' => $validated['cpf_cnpj'],
                'susep' => $validated['susep'],
                'email2' => $validated['email2'],
                'email3' => $validated['email3']
            ]);
            
            // 2. Atualizar vínculos com seguradoras (com auditoria)
            $seguradoras_atuais = $corretora->seguradoras->pluck('id')->toArray();
            $seguradoras_novas = $validated['seguradoras'] ?? [];
            
            // Remover seguradoras que não estão mais selecionadas
            $seguradoras_remover = array_diff($seguradoras_atuais, $seguradoras_novas);
            foreach ($seguradoras_remover as $seguradoraId) {
                CorretoraSeguradora::where('corretora_id', $corretora->id)
                    ->where('seguradora_id', $seguradoraId)
                    ->delete();
            }
            
            // Adicionar novas seguradoras
            $seguradoras_adicionar = array_diff($seguradoras_novas, $seguradoras_atuais);
            foreach ($seguradoras_adicionar as $seguradoraId) {
                CorretoraSeguradora::create([
                    'corretora_id' => $corretora->id,
                    'seguradora_id' => $seguradoraId
                ]);
            }
            
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
            
            if ($cotacoesCount > 0) {
                return redirect()
                    ->back()
                    ->with('error', 'Não é possível excluir esta corretora pois ela possui cotações associadas.');
            }

            // 1. Limpar relacionamentos nas pivots (com auditoria)
            CorretoraSeguradora::where('corretora_id', $corretora->id)->delete();
            
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