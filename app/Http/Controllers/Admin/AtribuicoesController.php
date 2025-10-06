<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Corretora;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AtribuicoesController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|diretor']);
    }

    /**
     * Lista todas as corretoras com seus comerciais responsáveis
     */
    public function index(Request $request)
    {
        // Buscar comerciais (users com role comercial/diretor)
        $comerciais = User::role(['comercial', 'diretor'])->orderBy('name')->get();
        
        // Query de corretoras
        $query = Corretora::with('usuario');
        
        // Filtros
        if ($request->filled('busca')) {
            $query->where('nome', 'like', '%' . $request->busca . '%');
        }
        
        if ($request->filled('comercial')) {
            if ($request->comercial === 'sem_responsavel') {
                $query->whereNull('usuario_id');
            } else {
                $query->where('usuario_id', $request->comercial);
            }
        }
        
        $corretoras = $query->orderBy('nome')->paginate(20);
        
        // Estatísticas (quantas corretoras cada comercial tem)
        $estatisticasQuery = Corretora::select('usuario_id', DB::raw('count(*) as total'))
            ->whereNotNull('usuario_id')
            ->with('usuario')
            ->groupBy('usuario_id')
            ->get();
        
        $estatisticas = [];
        foreach ($estatisticasQuery as $item) {
            $estatisticas[$item->usuario->name] = $item->total;
        }
        
        // Adicionar comerciais sem corretoras
        foreach ($comerciais as $comercial) {
            if (!isset($estatisticas[$comercial->name])) {
                $estatisticas[$comercial->name] = 0;
            }
        }
        
        // Adicionar corretoras sem responsável
        $semResponsavel = Corretora::whereNull('usuario_id')->count();
        if ($semResponsavel > 0) {
            $estatisticas['Sem Responsável'] = $semResponsavel;
        }
        
        return view('admin.atribuicoes', compact('corretoras', 'comerciais', 'estatisticas'));
    }

    /**
     * Atualiza o comercial responsável por uma corretora
     */
    public function update(Request $request, Corretora $corretora)
    {
        $validated = $request->validate([
            'usuario_id' => 'required|exists:users,id'
        ], [
            'usuario_id.required' => 'É obrigatório selecionar um responsável.',
            'usuario_id.exists' => 'O usuário selecionado não existe.'
        ]);
        
        // Verificar se o usuário selecionado tem role apropriada
        $novoResponsavel = User::findOrFail($validated['usuario_id']);
        if (!$novoResponsavel->hasAnyRole(['comercial', 'diretor', 'admin'])) {
            return redirect()
                ->back()
                ->with('error', 'O usuário selecionado não possui permissão para ser responsável por corretoras.');
        }
        
        $antigoResponsavel = $corretora->usuario->name ?? 'Nenhum';
        
        // Atualizar a corretora (auditoria automática via LogsActivity)
        $corretora->update(['usuario_id' => $validated['usuario_id']]);
        
        // Recarregar para pegar o novo responsável
        $corretora->load('usuario');
        $novoResponsavelNome = $corretora->usuario->name;
        
        return redirect()
            ->route('admin.atribuicoes')
            ->with('success', "Responsável da corretora '{$corretora->nome}' alterado de {$antigoResponsavel} para {$novoResponsavelNome}");
    }
}