<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Corretora;
use App\Models\Seguradora;
use Illuminate\Support\Facades\DB;
use Exception;

class CorretoraSeguradoraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('=== CORRETORA SEGURADORA SEEDER ===');
        
        // Dados extraídos do CSV - 560 relacionamentos
        $relacionamentos = $this->obterRelacionamentos();
        
        $this->command->info("📊 Total de relacionamentos: " . count($relacionamentos));
        
        // Limpar tabela antes de inserir
        $this->command->info("🗑️ Limpando tabela corretora_seguradora...");
        DB::table('corretora_seguradora')->truncate();
        
        // Processar em lotes de 100 para performance
        $lotes = array_chunk($relacionamentos, 100);
        $totalLotes = count($lotes);
        
        $contadores = [
            'inseridos' => 0,
            'erros' => 0,
            'por_seguradora' => []
        ];
        
        $this->command->info("🔄 Processando {$totalLotes} lotes de até 100 registros...");
        
        foreach ($lotes as $indice => $lote) {
            $this->command->info("📦 Processando lote " . ($indice + 1) . "/{$totalLotes}...");
            
            try {
                $dadosParaInserir = [];
                $agora = now();
                
                foreach ($lote as $relacionamento) {
                    $dadosParaInserir[] = [
                        'corretora_id' => $relacionamento['corretora_id'],
                        'seguradora_id' => $relacionamento['seguradora_id'],
                        'created_at' => $agora,
                        'updated_at' => $agora
                    ];
                    
                    // Contar por seguradora
                    $seguradoraId = $relacionamento['seguradora_id'];
                    $contadores['por_seguradora'][$seguradoraId] = 
                        ($contadores['por_seguradora'][$seguradoraId] ?? 0) + 1;
                }
                
                DB::table('corretora_seguradora')->insert($dadosParaInserir);
                $contadores['inseridos'] += count($dadosParaInserir);
                
                $this->command->info("✅ Lote " . ($indice + 1) . " inserido: " . count($dadosParaInserir) . " registros");
                
            } catch (Exception $e) {
                $this->command->error("❌ Erro no lote " . ($indice + 1) . ": " . $e->getMessage());
                $contadores['erros'] += count($lote);
            }
        }
        
        $this->gerarRelatorio($contadores);
    }
    
    /**
     * Obter array com os 560 relacionamentos extraídos do CSV
     */
    private function obterRelacionamentos()
    {
        // Processar CSV real para extrair relacionamentos corretos
        $path = storage_path('app/dados/relacao_corretoras.csv');
        
        if (!file_exists($path)) {
            $this->command->error("❌ Arquivo CSV não encontrado: {$path}");
            return [];
        }
        
        // Ler arquivo com encoding correto
        $conteudo = file_get_contents($path);
        if (!mb_check_encoding($conteudo, 'UTF-8')) {
            $conteudo = mb_convert_encoding($conteudo, 'UTF-8', 'ISO-8859-1');
        }
        
        $linhas = explode("\n", $conteudo);
        array_shift($linhas); // Remove header
        
        // Mapeamento correto das seguradoras (coluna => seguradora_id)
        $mapeamentoSeguradoras = [
            2 => 5,  // AIG = 5 (AIG SEGUROS BRASIL S.A.)
            3 => 2,  // AKAD = 2 (AKAD SEGUROS S.A.)  
            4 => 6,  // AVLA = 6 (AVLA SEGUROS BRASIL S.A.)
            5 => 8,  // DAYCOVAL = 8 (DAYCOVAL SEGUROS)
            6 => 7,  // ESSOR = 7 (ESSOR SEGURADORA S.A.)
            7 => 3,  // KOVR = 3 (KOVR SEGURADORA S A)
            8 => 9,  // MBM = 9 (MBM SEGURADORA S.A.)
            9 => 4,  // PRUDENTIAL = 4 (PRUDENTIAL DO BRASIL)
            10 => 1, // SWISS = 1 (SWISS RE CORPORATE SOLUTIONS)
        ];
        
        $relacionamentos = [];
        $corretorasNaoEncontradas = [];
        
        foreach ($linhas as $numeroLinha => $linha) {
            $linha = trim($linha);
            if (empty($linha)) continue;
            
            $dados = str_getcsv($linha, ';');
            if (count($dados) < 11) continue;
            
            $nomeCorretora = trim($dados[0]);
            if (empty($nomeCorretora)) continue;
            
            // Buscar corretora no banco
            $corretora = Corretora::where('nome', $nomeCorretora)->first();
            if (!$corretora) {
                $corretorasNaoEncontradas[] = $nomeCorretora;
                continue;
            }
            
            // Verificar marcações X nas colunas das seguradoras
            foreach ($mapeamentoSeguradoras as $coluna => $seguradoraId) {
                $valor = isset($dados[$coluna]) ? trim($dados[$coluna]) : '';
                if (strtoupper($valor) === 'X') {
                    $relacionamentos[] = [
                        'corretora_id' => $corretora->id,
                        'seguradora_id' => $seguradoraId
                    ];
                }
            }
        }
        
        if (!empty($corretorasNaoEncontradas)) {
            $this->command->warn("⚠️ " . count($corretorasNaoEncontradas) . " corretoras não encontradas no banco");
        }
        
        return $relacionamentos;
    }
    
    /**
     * Gerar relatório final com estatísticas
     */
    private function gerarRelatorio($contadores)
    {
        $this->command->info("\n=== RELATÓRIO DE PROCESSAMENTO ===");
        $this->command->info("📊 Total inserido: {$contadores['inseridos']}");
        $this->command->info("❌ Erros: {$contadores['erros']}");
        
        $this->command->info("\n=== DISTRIBUIÇÃO POR SEGURADORA ===");
        
        foreach ($contadores['por_seguradora'] as $seguradoraId => $quantidade) {
            $seguradora = Seguradora::find($seguradoraId);
            $nome = $seguradora ? $seguradora->nome : "ID {$seguradoraId}";
            $this->command->info("• {$nome}: {$quantidade} relacionamentos");
        }
        
        $totalFinal = DB::table('corretora_seguradora')->count();
        $this->command->info("\n📈 Total na tabela após inserção: {$totalFinal}");
        
        $this->command->info("\n✅ CorretoraSeguradoraSeeder concluído com sucesso!");
    }
}
