<?php

namespace App\Console\Commands;

use App\Models\Corretora;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UpdateCorretorasCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'corretoras:update-dados';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atualiza dados das corretoras a partir do CSV relacao_corretoras_update.csv';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🔄 Iniciando atualização de dados das corretoras...');
        
        // Verificar se arquivo existe
        $csvPath = storage_path('app/dados/relacao_corretoras_update.csv');
        if (!file_exists($csvPath)) {
            $this->error("❌ Arquivo não encontrado: {$csvPath}");
            return 1;
        }

        $this->info("📁 Lendo arquivo: {$csvPath}");

        // Abrir arquivo CSV
        $handle = fopen($csvPath, 'r');
        if (!$handle) {
            $this->error('❌ Erro ao abrir o arquivo CSV');
            return 1;
        }

        // Pular header
        fgetcsv($handle, 0, ';');

        $totalLinhas = 0;
        $corretorasAtualizadas = 0;
        $corretorasNaoEncontradas = 0;
        $erros = [];

        $this->info('📊 Processando dados...');
        $progressBar = $this->output->createProgressBar();

        // Processar cada linha
        while (($linha = fgetcsv($handle, 0, ';')) !== false) {
            $totalLinhas++;
            $progressBar->advance();

            // Validar se linha tem dados suficientes
            if (count($linha) < 10) {
                $erros[] = "Linha {$totalLinhas}: dados insuficientes";
                continue;
            }

            $nomeCorretora = trim($linha[0]);
            $sucCpd = trim($linha[1]);
            $estado = trim($linha[2]);
            $cidade = trim($linha[3]);
            $cpfCnpj = trim($linha[4]);
            $susep = trim($linha[5]);
            $email1 = trim($linha[6]);
            $email2 = trim($linha[7]);
            $email3 = trim($linha[8]);
            $fone = trim($linha[9]);

            // Buscar corretora pelo nome exato
            $corretora = Corretora::where('nome', $nomeCorretora)->first();

            if (!$corretora) {
                $corretorasNaoEncontradas++;
                continue;
            }

            try {
                // Atualizar dados (preservando usuario_id e relacionamentos)
                $corretora->update([
                    'suc_cpd' => $sucCpd ?: null,
                    'estado' => $estado ?: null,
                    'cidade' => $cidade ?: null,
                    'cpf_cnpj' => $cpfCnpj ?: null,
                    'susep' => $susep ?: null,
                    'email1' => $email1 ?: null,
                    'email2' => $email2 ?: null,
                    'email3' => $email3 ?: null,
                    'telefone' => $fone ?: $corretora->telefone // Preservar telefone existente se CSV vazio
                ]);

                $corretorasAtualizadas++;

            } catch (\Exception $e) {
                $erros[] = "Linha {$totalLinhas} ({$nomeCorretora}): {$e->getMessage()}";
            }
        }

        $progressBar->finish();
        fclose($handle);

        // Relatório final
        $this->newLine(2);
        $this->info('✅ Atualização concluída!');
        $this->line("📊 Relatório:");
        $this->line("   • Total de linhas processadas: {$totalLinhas}");
        $this->line("   • Corretoras atualizadas: {$corretorasAtualizadas}");
        $this->line("   • Corretoras não encontradas: {$corretorasNaoEncontradas}");
        
        if (count($erros) > 0) {
            $this->warn("⚠️  Erros encontrados: " . count($erros));
            foreach ($erros as $erro) {
                $this->error("   • {$erro}");
            }
        }

        // Log detalhado
        \Log::info('Atualização de corretoras concluída', [
            'total_linhas' => $totalLinhas,
            'atualizadas' => $corretorasAtualizadas,
            'nao_encontradas' => $corretorasNaoEncontradas,
            'erros' => count($erros),
            'arquivo' => $csvPath
        ]);

        return 0;
    }
}
