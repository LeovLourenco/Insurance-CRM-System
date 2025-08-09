<?php

namespace App\Http\Controllers;

use App\Models\Segurado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SeguradoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Segurado::query();

        // Filtro por busca
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filtro por tipo de pessoa
        if ($request->filled('tipo_pessoa') && in_array($request->tipo_pessoa, ['F', 'J'])) {
            $query->tipoPessoa($request->tipo_pessoa);
        }

        // Filtro por segurados com cotações
        if ($request->filled('com_cotacoes') && $request->com_cotacoes == '1') {
            $query->comCotacoes();
        }

        $segurados = $query->withCount(['cotacoes'])
                          ->latest()
                          ->paginate(10);

        return view('segurados.index', compact('segurados'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('segurados.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:191',
            'documento' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('segurados', 'documento')->where(function ($query) use ($request) {
                    // Limpa o documento para comparação
                    $documentoLimpo = preg_replace('/[^0-9]/', '', $request->documento);
                    return $query->where('documento', $documentoLimpo);
                }),
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $documentoLimpo = preg_replace('/[^0-9]/', '', $value);
                        $length = strlen($documentoLimpo);
                        
                        if ($length == 11) {
                            if (!Segurado::validarCPF($documentoLimpo)) {
                                $fail('O CPF informado é inválido.');
                            }
                        } elseif ($length == 14) {
                            if (!Segurado::validarCNPJ($documentoLimpo)) {
                                $fail('O CNPJ informado é inválido.');
                            }
                        } elseif ($length > 0) {
                            $fail('O documento deve ser um CPF (11 dígitos) ou CNPJ (14 dígitos) válido.');
                        }
                    }
                }
            ],
            'telefone' => 'nullable|string|max:20'
        ], [
            'nome.required' => 'O nome do segurado é obrigatório.',
            'nome.max' => 'O nome deve ter no máximo 191 caracteres.',
            'documento.unique' => 'Este documento já está cadastrado no sistema.',
            'documento.max' => 'O documento deve ter no máximo 20 caracteres.',
            'telefone.max' => 'O telefone deve ter no máximo 20 caracteres.'
        ]);

        try {
            DB::beginTransaction();
            
            $segurado = Segurado::create([
                'nome' => $validated['nome'],
                'documento' => $validated['documento'],
                'telefone' => $validated['telefone']
            ]);
            
            DB::commit();
            
            return redirect()
                ->route('segurados.index')
                ->with('success', 'Segurado criado com sucesso!');
                
        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao criar segurado: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Segurado $segurado)
    {
        // Carregar cotações recentes
        $cotacoes = $segurado->cotacoes()
                            ->with(['corretora', 'produto', 'cotacaoSeguradoras.seguradora'])
                            ->latest()
                            ->limit(10)
                            ->get();

        // Estatísticas de cotações por status
        $cotacoesPorStatus = $segurado->cotacoesPorStatus();

        return view('segurados.show', compact('segurado', 'cotacoes', 'cotacoesPorStatus'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Segurado $segurado)
    {
        return view('segurados.edit', compact('segurado'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Segurado $segurado)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:191',
            'documento' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('segurados', 'documento')->ignore($segurado->id)->where(function ($query) use ($request) {
                    // Limpa o documento para comparação
                    $documentoLimpo = preg_replace('/[^0-9]/', '', $request->documento);
                    return $query->where('documento', $documentoLimpo);
                }),
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $documentoLimpo = preg_replace('/[^0-9]/', '', $value);
                        $length = strlen($documentoLimpo);
                        
                        if ($length == 11) {
                            if (!Segurado::validarCPF($documentoLimpo)) {
                                $fail('O CPF informado é inválido.');
                            }
                        } elseif ($length == 14) {
                            if (!Segurado::validarCNPJ($documentoLimpo)) {
                                $fail('O CNPJ informado é inválido.');
                            }
                        } elseif ($length > 0) {
                            $fail('O documento deve ser um CPF (11 dígitos) ou CNPJ (14 dígitos) válido.');
                        }
                    }
                }
            ],
            'telefone' => 'nullable|string|max:20'
        ], [
            'nome.required' => 'O nome do segurado é obrigatório.',
            'nome.max' => 'O nome deve ter no máximo 191 caracteres.',
            'documento.unique' => 'Este documento já está cadastrado no sistema.',
            'documento.max' => 'O documento deve ter no máximo 20 caracteres.',
            'telefone.max' => 'O telefone deve ter no máximo 20 caracteres.'
        ]);

        try {
            DB::beginTransaction();
            
            $segurado->update([
                'nome' => $validated['nome'],
                'documento' => $validated['documento'],
                'telefone' => $validated['telefone']
            ]);
            
            DB::commit();
            
            return redirect()
                ->route('segurados.show', $segurado)
                ->with('success', 'Segurado atualizado com sucesso!');
                
        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar segurado: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Segurado $segurado)
    {
        try {
            DB::beginTransaction();
            
            // Verificar se segurado está sendo usado
            $cotacoesCount = $segurado->cotacoes()->count();
            $vinculosCount = $segurado->vinculos()->count();
            
            if ($cotacoesCount > 0 || $vinculosCount > 0) {
                return redirect()
                    ->back()
                    ->with('error', 'Não é possível excluir este segurado pois ele possui cotações ou vínculos associados.');
            }

            // Deletar o segurado
            $segurado->delete();
            
            DB::commit();
            
            return redirect()
                ->route('segurados.index')
                ->with('success', 'Segurado excluído com sucesso!');
                
        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()
                ->back()
                ->with('error', 'Erro ao excluir segurado: ' . $e->getMessage());
        }
    }

    /**
     * Método para busca via AJAX (opcional)
     */
    public function search(Request $request)
    {
        $search = $request->get('q');
        
        $segurados = Segurado::where('nome', 'like', "%{$search}%")
                            ->orWhere('documento', 'like', "%{$search}%")
                            ->limit(10)
                            ->get(['id', 'nome', 'documento', 'telefone']);

        // Adicionar formatação para retorno JSON
        $segurados->transform(function ($segurado) {
            return [
                'id' => $segurado->id,
                'nome' => $segurado->nome,
                'documento' => $segurado->documento_formatado,
                'telefone' => $segurado->telefone_formatado,
                'tipo_pessoa' => $segurado->tipo_pessoa_texto
            ];
        });

        return response()->json($segurados);
    }

    /**
     * Validar documento via AJAX
     */
    public function validarDocumento(Request $request)
    {
        $documento = $request->get('documento');
        
        if (!$documento) {
            return response()->json(['valido' => false, 'mensagem' => 'Documento não informado']);
        }
        
        $documentoLimpo = preg_replace('/[^0-9]/', '', $documento);
        $length = strlen($documentoLimpo);
        
        if ($length == 11) {
            $valido = Segurado::validarCPF($documentoLimpo);
            $tipo = 'CPF';
        } elseif ($length == 14) {
            $valido = Segurado::validarCNPJ($documentoLimpo);
            $tipo = 'CNPJ';
        } else {
            return response()->json([
                'valido' => false, 
                'mensagem' => 'Documento deve ter 11 (CPF) ou 14 (CNPJ) dígitos'
            ]);
        }
        
        // Verificar se já existe
        $existe = Segurado::where('documento', $documentoLimpo)->exists();
        
        return response()->json([
            'valido' => $valido,
            'tipo' => $tipo,
            'existe' => $existe,
            'mensagem' => $valido ? 
                ($existe ? "{$tipo} válido, mas já cadastrado no sistema" : "{$tipo} válido") : 
                "{$tipo} inválido"
        ]);
    }
}