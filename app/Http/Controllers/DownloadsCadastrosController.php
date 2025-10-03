<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CadastroCorretor;
use Carbon\Carbon;
use Illuminate\Http\Response;

class DownloadsCadastrosController extends Controller
{
    /**
     * Construtor - verificação de permissão admin
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->hasRole('admin')) {
                abort(403, 'Acesso negado. Apenas administradores podem acessar esta funcionalidade.');
            }
            return $next($request);
        });
    }

    /**
     * Exibe a página de downloads com lista de cadastros
     */
    public function index(Request $request)
    {
        // Filtros
        $filtros = [
            'data_inicio' => $request->get('data_inicio'),
            'data_fim' => $request->get('data_fim'),
            'corretora' => $request->get('corretora'),
        ];

        // Query base
        $query = CadastroCorretor::orderBy('data_hora', 'desc');

        // Aplicar filtros
        if ($filtros['data_inicio']) {
            $query->whereDate('data_hora', '>=', $filtros['data_inicio']);
        }

        if ($filtros['data_fim']) {
            $query->whereDate('data_hora', '<=', $filtros['data_fim']);
        }


        if ($filtros['corretora']) {
            $query->where('corretora', 'like', '%' . $filtros['corretora'] . '%');
        }

        // Paginação
        $cadastros = $query->paginate(25)->withQueryString();

        // Estatísticas
        $estatisticas = [
            'total' => CadastroCorretor::count(),
            'hoje' => CadastroCorretor::whereDate('data_hora', Carbon::today())->count(),
            'semana' => CadastroCorretor::whereBetween('data_hora', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])->count(),
            'mes' => CadastroCorretor::whereYear('data_hora', Carbon::now()->year)
                ->whereMonth('data_hora', Carbon::now()->month)
                ->count(),
        ];

        return view('downloads.cadastros-corretores', compact(
            'cadastros',
            'filtros',
            'estatisticas'
        ));
    }

    /**
     * Gera e baixa o arquivo CSV
     */
    public function downloadCSV(Request $request)
    {
        // Filtros (mesma lógica do index)
        $query = CadastroCorretor::orderBy('data_hora', 'asc');

        if ($request->has('data_inicio') && $request->data_inicio) {
            $query->whereDate('data_hora', '>=', $request->data_inicio);
        }

        if ($request->has('data_fim') && $request->data_fim) {
            $query->whereDate('data_hora', '<=', $request->data_fim);
        }


        if ($request->has('corretora') && $request->corretora) {
            $query->where('corretora', 'like', '%' . $request->corretora . '%');
        }

        // Buscar todos os dados (sem paginação para CSV)
        $cadastros = $query->get();

        // Nome do arquivo com data atual
        $nomeArquivo = 'cadastros_corretores_' . Carbon::now()->format('Y-m-d') . '.csv';

        // Headers para download
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nomeArquivo . '"',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
            'Pragma' => 'public'
        ];

        // Callback para gerar o CSV
        $callback = function() use ($cadastros) {
            $file = fopen('php://output', 'w');
            
            // Adicionar BOM UTF-8 para Excel
            fwrite($file, "\xEF\xBB\xBF");

            // Cabeçalhos das colunas
            fputcsv($file, [
                'Data/Hora',
                'Corretora', 
                'CNPJ',
                'Email',
                'Responsável',
                'Telefone',
                'Seguradoras'
            ], ';');

            // Dados
            foreach ($cadastros as $cadastro) {
                fputcsv($file, [
                    $cadastro->data_hora->format('d/m/Y H:i:s'),
                    $cadastro->corretora,
                    $cadastro->cnpj_formatado ?? $cadastro->cnpj,
                    $cadastro->email,
                    $cadastro->responsavel,
                    $cadastro->telefone_formatado ?? $cadastro->telefone,
                    $cadastro->seguradoras_formatada ?? $cadastro->seguradoras
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
