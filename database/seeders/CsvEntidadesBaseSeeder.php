<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Corretora;
use App\Models\User;

class CsvEntidadesBaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('=== CSV ENTIDADES BASE SEEDER ===');
        
        // 1. VERIFICAR arquivo CSV
        $path = storage_path('app/dados/relacao_corretoras.csv');
        
        if (!file_exists($path)) {
            $this->command->error("❌ Arquivo não encontrado: {$path}");
            return;
        }
        
        $this->command->info("✅ Arquivo encontrado: " . basename($path));
        $this->command->info("📊 Tamanho: " . number_format(filesize($path) / 1024, 1) . " KB");
        
        // 2. CONFIGURAR mapeamento de responsáveis
        $mapeamentoResponsaveis = $this->configurarMapeamento();
        
        // 3. LER e processar CSV
        $this->processarCsv($path, $mapeamentoResponsaveis);
    }
    
    private function configurarMapeamento()
    {
        $this->command->info("\n=== CONFIGURANDO MAPEAMENTO DE RESPONSÁVEIS ===");
        
        // Buscar IDs dos usuários reais
        $claudia = User::where('email', 'claudia.bavaresco@inovarepresentacao.com.br')->first();
        $marcelo = User::where('email', 'marcelo.ceroni@inovarepresentacao.com.br')->first();  
        $juliana = User::where('email', 'juliana.specht@inovarepresentacao.com.br')->first();
        
        // Usuários restantes para round-robin
        $usuariosRestantes = User::whereIn('email', [
            'cintia.garcia@inovarepresentacao.com.br',      // ID 8
            'daiane.escouto@inovarepresentacao.com.br',     // ID 10  
            'julia.silva@inovarepresentacao.com.br',        // ID 11
            'kelly.santos@inovarepresentacao.com.br',       // ID 13
            'luciana.oliveira@inovarepresentacao.com.br',   // ID 14
            'roberta.silva@inovarepresentacao.com.br'       // ID 16
        ])->get();
        
        $mapeamento = [
            'definir' => $claudia ? $claudia->id : null,
            'ceroni' => $marcelo ? $marcelo->id : null,
            'juliana' => $juliana ? $juliana->id : null,
            'round_robin' => $usuariosRestantes->pluck('id')->toArray()
        ];
        
        $this->command->info("• Definir → " . ($claudia ? $claudia->name : 'NÃO ENCONTRADO'));
        $this->command->info("• Ceroni → " . ($marcelo ? $marcelo->name : 'NÃO ENCONTRADO'));  
        $this->command->info("• Juliana → " . ($juliana ? $juliana->name : 'NÃO ENCONTRADO'));
        $this->command->info("• Round-robin: " . count($mapeamento['round_robin']) . " usuários");
        
        return $mapeamento;
    }
    
    private function processarCsv($path, $mapeamentoResponsaveis)
    {
        $this->command->info("\n=== PROCESSANDO CSV ===");
        
        // Ler arquivo tratando encoding
        $conteudo = file_get_contents($path);
        
        // Tentar detectar e converter encoding
        if (!mb_check_encoding($conteudo, 'UTF-8')) {
            $conteudo = mb_convert_encoding($conteudo, 'UTF-8', 'ISO-8859-1');
            $this->command->info("🔄 Encoding convertido para UTF-8");
        }
        
        $linhas = explode("\n", $conteudo);
        $header = str_getcsv(array_shift($linhas), ';'); // Remove cabeçalho
        
        $this->command->info("📋 Colunas: " . implode(', ', array_slice($header, 0, 5)) . '...');
        $this->command->info("📝 Linhas a processar: " . count($linhas));
        
        // Contadores para relatório
        $contadores = [
            'total' => 0,
            'criadas' => 0,
            'atualizadas' => 0,
            'erros' => 0,
            'por_usuario' => []
        ];
        
        $roundRobinIndex = 0;
        
        foreach ($linhas as $numeroLinha => $linha) {
            $linha = trim($linha);
            if (empty($linha)) continue;
            
            $contadores['total']++;
            
            try {
                $dados = str_getcsv($linha, ';');
                
                if (count($dados) < 2) {
                    $this->command->warn("⚠️ Linha {$numeroLinha}: dados insuficientes");
                    $contadores['erros']++;
                    continue;
                }
                
                $nomeCorretora = trim($dados[0]);
                $responsavel = strtolower(trim($dados[1]));
                
                if (empty($nomeCorretora)) {
                    $contadores['erros']++;
                    continue;
                }
                
                // Mapear responsável
                $usuarioId = $this->mapearResponsavel($responsavel, $mapeamentoResponsaveis, $roundRobinIndex);
                
                // Extrair outros dados opcionais
                $email = isset($dados[15]) ? trim($dados[15]) : null;
                $telefone = isset($dados[18]) ? trim($dados[18]) : null;
                
                // Criar ou atualizar corretora
                $corretora = Corretora::firstOrCreate(
                    ['nome' => $nomeCorretora],
                    [
                        'email' => $email ?: null,
                        'telefone' => $telefone ?: null,
                        'usuario_id' => $usuarioId
                    ]
                );
                
                if ($corretora->wasRecentlyCreated) {
                    $contadores['criadas']++;
                } else {
                    // Atualizar usuario_id se não existia
                    if (!$corretora->usuario_id && $usuarioId) {
                        $corretora->update(['usuario_id' => $usuarioId]);
                        $contadores['atualizadas']++;
                    }
                }
                
                // Contar por usuario
                if ($usuarioId) {
                    $contadores['por_usuario'][$usuarioId] = ($contadores['por_usuario'][$usuarioId] ?? 0) + 1;
                }
                
            } catch (\Exception $e) {
                $this->command->error("❌ Erro na linha {$numeroLinha}: " . $e->getMessage());
                $contadores['erros']++;
            }
        }
        
        $this->gerarRelatorio($contadores);
    }
    
    private function mapearResponsavel($responsavel, $mapeamento, &$roundRobinIndex)
    {
        if (str_contains($responsavel, 'definir')) {
            return $mapeamento['definir'];
        }
        
        if (str_contains($responsavel, 'ceroni')) {
            return $mapeamento['ceroni'];
        }
        
        if (str_contains($responsavel, 'juliana')) {
            return $mapeamento['juliana'];
        }
        
        // Round-robin para outros valores
        if (count($mapeamento['round_robin']) > 0) {
            $usuarioId = $mapeamento['round_robin'][$roundRobinIndex % count($mapeamento['round_robin'])];
            $roundRobinIndex++;
            return $usuarioId;
        }
        
        return $mapeamento['definir']; // Fallback para Claudia
    }
    
    private function gerarRelatorio($contadores)
    {
        $this->command->info("\n=== RELATÓRIO DE PROCESSAMENTO ===");
        $this->command->info("📊 Total processado: {$contadores['total']}");
        $this->command->info("✅ Criadas: {$contadores['criadas']}");
        $this->command->info("🔄 Atualizadas: {$contadores['atualizadas']}");
        $this->command->info("❌ Erros: {$contadores['erros']}");
        
        $this->command->info("\n=== DISTRIBUIÇÃO POR USUÁRIO ===");
        
        foreach ($contadores['por_usuario'] as $usuarioId => $quantidade) {
            $usuario = User::find($usuarioId);
            $nome = $usuario ? $usuario->name : "ID {$usuarioId}";
            $this->command->info("• {$nome}: {$quantidade} corretoras");
        }
        
        $totalAtribuidas = array_sum($contadores['por_usuario']);
        $this->command->info("\n📈 Total de corretoras atribuídas: {$totalAtribuidas}");
        
        $this->command->info("\n✅ CsvEntidadesBaseSeeder concluído com sucesso!");
    }
}
