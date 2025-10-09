<?php

namespace App\Http\Controllers;

use App\Models\Apolice;
use App\Models\Corretora;
use App\Models\Seguradora;
use App\Models\Produto;
use App\Models\Segurado;
use App\Models\Cotacao;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ApoliceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Apolice::query();
        $user = auth()->user();

        // ✅ ISOLAMENTO POR ROLE
        if ($user->hasRole('comercial')) {
            // Comerciais veem apenas suas próprias (via cotação) ou importadas (sem cotação)
            $query->where(function($q) use ($user) {
                $q->whereHas('cotacao', function($cotacaoQuery) use ($user) {
                    $cotacaoQuery->where('user_id', $user->id);
                })->orWhereNull('cotacao_id'); // Importadas sem cotação
            });
        }
        // Admin e Diretor veem todas (Diretor pode ter restrições específicas no futuro)

        // Eager loading para evitar N+1
        $query->with(['corretora', 'seguradora', 'produto', 'segurado', 'cotacao', 'usuario']);

        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('corretora')) {
            $query->where('corretora_id', $request->corretora);
        }

        if ($request->filled('seguradora')) {
            $query->where('seguradora_id', $request->seguradora);
        }

        if ($request->filled('data_inicio_vigencia')) {
            $query->whereDate('inicio_vigencia', '>=', $request->data_inicio_vigencia);
        }

        if ($request->filled('data_fim_vigencia')) {
            $query->whereDate('fim_vigencia', '<=', $request->data_fim_vigencia);
        }

        if ($request->filled('origem')) {
            $query->where('origem', $request->origem);
        }

        // Busca por número da apólice
        if ($request->filled('busca')) {
            $query->where(function($q) use ($request) {
                $q->where('numero_apolice', 'like', '%' . $request->busca . '%')
                  ->orWhere('nome_segurado', 'like', '%' . $request->busca . '%')
                  ->orWhere('nome_corretor', 'like', '%' . $request->busca . '%');
            });
        }

        // Ordenação e paginação
        $apolices = $query->latest()->paginate(15)->withQueryString();

        // Dados para filtros
        $corretoras = Corretora::orderBy('nome')->get();
        $seguradoras = Seguradora::orderBy('nome')->get();

        return view('apolices.index', compact('apolices', 'corretoras', 'seguradoras'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $corretoras = Corretora::orderBy('nome')->get();
        $seguradoras = Seguradora::orderBy('nome')->get();
        $produtos = Produto::orderBy('nome')->get();
        $segurados = Segurado::orderBy('nome')->get();

        return view('apolices.create', compact('corretoras', 'seguradoras', 'produtos', 'segurados'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cotacao_id' => 'nullable|exists:cotacoes,id',
            'numero_apolice' => 'nullable|string|max:191|unique:apolices,numero_apolice',
            'status' => 'required|in:pendente_emissao,ativa,renovacao,cancelada',
            'nome_segurado' => 'nullable|string|max:191',
            'cnpj_segurado' => 'nullable|string|max:18',
            'nome_corretor' => 'nullable|string|max:191',
            'cnpj_corretor' => 'nullable|string|max:18',
            'seguradora_id' => 'nullable|exists:seguradoras,id',
            'segurado_id' => 'nullable|exists:segurados,id',
            'corretora_id' => 'nullable|exists:corretoras,id',
            'produto_id' => 'nullable|exists:produtos,id',
            'premio_liquido' => 'nullable|numeric|min:0|max:999999999999.99',
            'data_emissao' => 'nullable|date',
            'inicio_vigencia' => 'nullable|date',
            'fim_vigencia' => 'nullable|date|after_or_equal:inicio_vigencia',
            'endosso' => 'nullable|string|max:20',
            'parcela' => 'nullable|integer|min:0',
            'total_parcelas' => 'nullable|integer|min:0',
            'ramo' => 'nullable|string|max:10',
            'linha_produto' => 'nullable|string|max:191',
            'data_pagamento' => 'nullable|date',
            'origem' => 'required|in:cotacao,importacao',
            'observacoes_endosso' => 'nullable|string',
            'arquivo_sharepoint' => 'nullable|string|max:191'
        ], [
            'numero_apolice.unique' => 'Já existe uma apólice com este número.',
            'fim_vigencia.after_or_equal' => 'A data de fim da vigência deve ser posterior ou igual ao início.',
            'premio_liquido.max' => 'O valor do prêmio é muito alto.',
            'status.required' => 'O status da apólice é obrigatório.',
            'status.in' => 'Status inválido.',
            'origem.required' => 'A origem da apólice é obrigatória.'
        ]);

        try {
            DB::beginTransaction();

            // Adicionar usuário atual se não especificado
            if (!isset($validated['usuario_id'])) {
                $validated['usuario_id'] = auth()->id();
            }

            $apolice = Apolice::create($validated);

            DB::commit();

            return redirect()
                ->route('apolices.show', $apolice)
                ->with('success', 'Apólice criada com sucesso!');

        } catch (\Exception $e) {
            DB::rollback();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao criar apólice: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Apolice $apolice)
    {
        // Verificar autorização baseada em role
        $user = auth()->user();
        if ($user->hasRole('comercial')) {
            // Comercial só pode ver suas próprias ou importadas
            if ($apolice->cotacao && $apolice->cotacao->user_id !== $user->id && $apolice->cotacao_id !== null) {
                abort(403, 'Você não tem permissão para ver esta apólice.');
            }
        }

        // Carregar todos os relacionamentos
        $apolice->load([
            'cotacao.user',
            'seguradora',
            'segurado',
            'corretora.usuario',
            'produto',
            'usuario'
        ]);

        return view('apolices.show', compact('apolice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Apolice $apolice)
    {
        // Verificar autorização baseada em role
        $user = auth()->user();
        if ($user->hasRole('comercial')) {
            // Comercial só pode editar suas próprias ou importadas
            if ($apolice->cotacao && $apolice->cotacao->user_id !== $user->id && $apolice->cotacao_id !== null) {
                abort(403, 'Você não tem permissão para editar esta apólice.');
            }
        }

        $corretoras = Corretora::orderBy('nome')->get();
        $seguradoras = Seguradora::orderBy('nome')->get();
        $produtos = Produto::orderBy('nome')->get();
        $segurados = Segurado::orderBy('nome')->get();

        return view('apolices.edit', compact('apolice', 'corretoras', 'seguradoras', 'produtos', 'segurados'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Apolice $apolice)
    {
        // Verificar autorização baseada em role
        $user = auth()->user();
        if ($user->hasRole('comercial')) {
            // Comercial só pode editar suas próprias ou importadas
            if ($apolice->cotacao && $apolice->cotacao->user_id !== $user->id && $apolice->cotacao_id !== null) {
                abort(403, 'Você não tem permissão para editar esta apólice.');
            }
        }

        $validated = $request->validate([
            'numero_apolice' => 'nullable|string|max:191|unique:apolices,numero_apolice,' . $apolice->id,
            'status' => 'required|in:pendente_emissao,ativa,renovacao,cancelada',
            'nome_segurado' => 'nullable|string|max:191',
            'cnpj_segurado' => 'nullable|string|max:18',
            'nome_corretor' => 'nullable|string|max:191',
            'cnpj_corretor' => 'nullable|string|max:18',
            'seguradora_id' => 'nullable|exists:seguradoras,id',
            'segurado_id' => 'nullable|exists:segurados,id',
            'corretora_id' => 'nullable|exists:corretoras,id',
            'produto_id' => 'nullable|exists:produtos,id',
            'premio_liquido' => 'nullable|numeric|min:0|max:999999999999.99',
            'data_emissao' => 'nullable|date',
            'inicio_vigencia' => 'nullable|date',
            'fim_vigencia' => 'nullable|date|after_or_equal:inicio_vigencia',
            'endosso' => 'nullable|string|max:20',
            'parcela' => 'nullable|integer|min:0',
            'total_parcelas' => 'nullable|integer|min:0',
            'ramo' => 'nullable|string|max:10',
            'linha_produto' => 'nullable|string|max:191',
            'data_pagamento' => 'nullable|date',
            'observacoes_endosso' => 'nullable|string',
            'arquivo_sharepoint' => 'nullable|string|max:191'
        ], [
            'numero_apolice.unique' => 'Já existe uma apólice com este número.',
            'fim_vigencia.after_or_equal' => 'A data de fim da vigência deve ser posterior ou igual ao início.',
            'premio_liquido.max' => 'O valor do prêmio é muito alto.',
            'status.required' => 'O status da apólice é obrigatório.',
            'status.in' => 'Status inválido.'
        ]);

        try {
            DB::beginTransaction();

            $apolice->update($validated);

            DB::commit();

            return redirect()
                ->route('apolices.show', $apolice)
                ->with('success', 'Apólice atualizada com sucesso!');

        } catch (\Exception $e) {
            DB::rollback();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar apólice: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Apolice $apolice)
    {
        // Verificar autorização baseada em role
        $user = auth()->user();
        if ($user->hasRole('comercial')) {
            abort(403, 'Comerciais não podem excluir apólices.');
        }

        try {
            DB::beginTransaction();

            // Verificar se a apólice pode ser excluída
            if ($apolice->status === 'ativa' && $apolice->estaVigente()) {
                return redirect()
                    ->back()
                    ->with('error', 'Não é possível excluir uma apólice ativa e vigente.');
            }

            $numero = $apolice->numero_apolice ?? 'ID: ' . $apolice->id;

            // Soft delete se o modelo suportar, senão delete normal
            if (method_exists($apolice, 'trashed')) {
                $apolice->delete(); // Soft delete
            } else {
                $apolice->forceDelete(); // Delete normal
            }

            DB::commit();

            return redirect()
                ->route('apolices.index')
                ->with('success', "Apólice {$numero} excluída com sucesso!");

        } catch (\Exception $e) {
            DB::rollback();

            return redirect()
                ->back()
                ->with('error', 'Erro ao excluir apólice: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar form de importação de Excel
     */
    public function importForm()
    {
        return view('apolices.import');
    }

    /**
     * Processar importação de Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'arquivo' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB
            'metodo_matching' => 'nullable|string|max:50'
        ], [
            'arquivo.required' => 'É obrigatório selecionar um arquivo.',
            'arquivo.mimes' => 'O arquivo deve ser Excel (.xlsx, .xls) ou CSV.',
            'arquivo.max' => 'O arquivo deve ter no máximo 10MB.'
        ]);

        try {
            DB::beginTransaction();

            // TODO: Implementar lógica de importação de Excel
            // Por enquanto, estrutura básica
            $arquivo = $request->file('arquivo');
            $nomeArquivo = time() . '_' . $arquivo->getClientOriginalName();
            $caminhoArquivo = $arquivo->storeAs('imports/apolices', $nomeArquivo);

            // Contador de registros para demonstração
            $registrosImportados = 0;
            $registrosComErro = 0;

            // Aqui seria a lógica de processamento do Excel
            // Exemplo de estrutura:
            /*
            $spreadsheet = IOFactory::load(storage_path('app/' . $caminhoArquivo));
            $worksheet = $spreadsheet->getActiveSheet();
            
            foreach ($worksheet->getRowIterator(2) as $row) {
                try {
                    $data = [
                        'numero_apolice' => $worksheet->getCell('A' . $row->getRowIndex())->getValue(),
                        'nome_segurado' => $worksheet->getCell('B' . $row->getRowIndex())->getValue(),
                        // ... outros campos
                        'origem' => 'importacao',
                        'arquivo_sharepoint' => $nomeArquivo,
                        'metodo_matching' => $request->metodo_matching ?? 'importacao_manual',
                        'usuario_id' => auth()->id()
                    ];
                    
                    Apolice::create($data);
                    $registrosImportados++;
                    
                } catch (\Exception $e) {
                    $registrosComErro++;
                }
            }
            */

            // Por enquanto, simular importação
            $registrosImportados = 0; // Será atualizado quando implementar

            DB::commit();

            $mensagem = "Importação concluída! {$registrosImportados} apólices importadas";
            if ($registrosComErro > 0) {
                $mensagem .= " ({$registrosComErro} com erro)";
            }

            return redirect()
                ->route('apolices.index')
                ->with('success', $mensagem);

        } catch (\Exception $e) {
            DB::rollback();

            return redirect()
                ->back()
                ->with('error', 'Erro na importação: ' . $e->getMessage());
        }
    }

    /**
     * Criar apólice a partir de cotação (quando status muda para em_emissao)
     */
    public function criarDeCotacao(Request $request)
    {
        $request->validate([
            'cotacao_id' => 'required|exists:cotacoes,id',
            'numero_apolice' => 'nullable|string|max:191|unique:apolices,numero_apolice',
            'data_emissao' => 'nullable|date',
            'inicio_vigencia' => 'nullable|date',
            'fim_vigencia' => 'nullable|date|after_or_equal:inicio_vigencia',
            'premio_liquido' => 'nullable|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            $cotacao = Cotacao::with(['corretora', 'produto', 'segurado'])->findOrFail($request->cotacao_id);

            // Verificar se usuário pode criar apólice para esta cotação
            $user = auth()->user();
            if ($user->hasRole('comercial') && $cotacao->user_id !== $user->id) {
                abort(403, 'Você não pode criar apólice para cotação de outro comercial.');
            }

            // Verificar se já existe apólice para esta cotação
            if ($cotacao->apolices()->count() > 0) {
                return redirect()
                    ->back()
                    ->with('error', 'Já existe apólice(s) para esta cotação.');
            }

            // Criar apólice baseada na cotação
            $apolice = Apolice::create([
                'cotacao_id' => $cotacao->id,
                'numero_apolice' => $request->numero_apolice,
                'status' => 'pendente_emissao',
                'origem' => 'cotacao',
                'corretora_id' => $cotacao->corretora_id,
                'produto_id' => $cotacao->produto_id,
                'segurado_id' => $cotacao->segurado_id,
                'usuario_id' => $cotacao->user_id,
                'data_emissao' => $request->data_emissao,
                'inicio_vigencia' => $request->inicio_vigencia,
                'fim_vigencia' => $request->fim_vigencia,
                'premio_liquido' => $request->premio_liquido
            ]);

            // Atualizar status da cotação
            $cotacao->update(['status' => Cotacao::STATUS_EM_EMISSAO]);

            DB::commit();

            return redirect()
                ->route('apolices.show', $apolice)
                ->with('success', 'Apólice criada a partir da cotação com sucesso!');

        } catch (\Exception $e) {
            DB::rollback();

            return redirect()
                ->back()
                ->with('error', 'Erro ao criar apólice: ' . $e->getMessage());
        }
    }
}
