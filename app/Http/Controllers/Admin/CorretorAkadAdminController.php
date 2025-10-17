<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CorretorAkad;
use Illuminate\Http\Request;

class CorretorAkadAdminController extends Controller
{
    /**
     * Lista de corretores AKAD
     */
    public function index(Request $request)
    {
        $query = CorretorAkad::query()
            ->with('documentoAtivo')
            ->orderBy('created_at', 'desc');
        
        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('razao_social', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('cnpj', 'like', "%{$search}%")
                  ->orWhere('codigo_susep', 'like', "%{$search}%")
                  ->orWhere('nome', 'like', "%{$search}%");
            });
        }
        
        $corretores = $query->paginate(15);
        
        // Estatísticas
        $stats = [
            'total' => CorretorAkad::count(),
            'pendente' => CorretorAkad::where('status', 'pendente')->count(),
            'documento_enviado' => CorretorAkad::where('status', 'documento_enviado')->count(),
            'assinado' => CorretorAkad::where('status', 'assinado')->count(),
        ];
        
        return view('admin.corretores-akad.index', compact('corretores', 'stats'));
    }
    
    /**
     * Detalhes do corretor
     */
    public function show($id)
    {
        $corretor = CorretorAkad::with(['documentos', 'logs'])->findOrFail($id);
        
        return view('admin.corretores-akad.show', compact('corretor'));
    }
    
    /**
     * Reenviar documento
     */
    public function reenviarDocumento($id)
    {
        $corretor = CorretorAkad::findOrFail($id);
        
        try {
            $autentiqueService = app(\App\Services\AutentiqueService::class);
            $resultado = $autentiqueService->criarDocumentoCorretor($corretor);
            
            if ($resultado['success']) {
                $corretor->marcarDocumentoEnviado($resultado['documento_id'], null);
                
                return redirect()
                    ->route('admin.corretores-akad.show', $id)
                    ->with('success', 'Documento reenviado com sucesso!');
            }
            
            return redirect()
                ->route('admin.corretores-akad.show', $id)
                ->with('error', 'Erro ao reenviar documento: ' . $resultado['error']);
                
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.corretores-akad.show', $id)
                ->with('error', 'Erro interno: ' . $e->getMessage());
        }
    }
}