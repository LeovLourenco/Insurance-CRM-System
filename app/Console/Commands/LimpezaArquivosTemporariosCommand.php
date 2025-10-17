<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AutentiqueService;
use Exception;

class LimpezaArquivosTemporariosCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'autentique:cleanup-temp 
                           {--force : Força a limpeza sem confirmação}
                           {--hours=1 : Arquivos mais antigos que X horas (padrão: 1)}';

    /**
     * The console command description.
     */
    protected $description = 'Limpa arquivos temporários antigos do sistema de templates Autentique';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->info('🧹 Iniciando limpeza de arquivos temporários...');

            $autentiqueService = app(AutentiqueService::class);
            
            if (!$this->option('force')) {
                if (!$this->confirm('Deseja continuar com a limpeza de arquivos temporários?')) {
                    $this->info('Operação cancelada pelo usuário.');
                    return Command::SUCCESS;
                }
            }

            $deletados = $autentiqueService->limpezaAutomatica();

            if ($deletados > 0) {
                $this->info("✅ Limpeza concluída! {$deletados} arquivo(s) temporário(s) removido(s).");
            } else {
                $this->info('ℹ️ Nenhum arquivo temporário antigo encontrado para remoção.');
            }

            // Mostrar estatísticas
            $this->mostrarEstatisticas($autentiqueService);

            return Command::SUCCESS;

        } catch (Exception $e) {
            $this->error("❌ Erro durante a limpeza: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    /**
     * Mostrar estatísticas do sistema
     */
    protected function mostrarEstatisticas($autentiqueService)
    {
        try {
            $this->newLine();
            $this->info('📊 Estatísticas do Sistema:');

            // Validar configuração
            $config = $autentiqueService->validarConfiguracao();
            
            if ($config['valido']) {
                $this->line('   ✅ Configuração válida');
            } else {
                $this->line('   ❌ Problemas na configuração:');
                foreach ($config['erros'] as $erro) {
                    $this->line("      - {$erro}");
                }
            }

            // Listar templates
            $templates = $autentiqueService->listarTemplates();
            
            if ($templates['success']) {
                $count = count($templates['templates']);
                $this->line("   📄 Templates disponíveis: {$count}");
                
                foreach ($templates['templates'] as $template) {
                    if (isset($template['error'])) {
                        $this->line("      ❌ {$template['name']}: {$template['error']}");
                    } else {
                        $vars = $template['variables_count'] ?? 0;
                        $size = $this->formatBytes($template['size'] ?? 0);
                        $this->line("      ✅ {$template['name']} ({$vars} variáveis, {$size})");
                    }
                }
            }

            // Verificar diretórios
            $this->verificarDiretorios();

        } catch (Exception $e) {
            $this->warn("Não foi possível obter estatísticas: {$e->getMessage()}");
        }
    }

    /**
     * Verificar status dos diretórios
     */
    protected function verificarDiretorios()
    {
        $diretorios = [
            'Templates' => storage_path('app/templates'),
            'Temporários' => storage_path('app/temp')
        ];

        foreach ($diretorios as $nome => $caminho) {
            if (is_dir($caminho)) {
                $arquivos = count(glob($caminho . '/*'));
                $size = $this->getDirSize($caminho);
                $permissions = substr(sprintf('%o', fileperms($caminho)), -4);
                
                $this->line("   📁 {$nome}: {$arquivos} arquivo(s), {$this->formatBytes($size)}, perm: {$permissions}");
            } else {
                $this->line("   ❌ {$nome}: Diretório não encontrado");
            }
        }
    }

    /**
     * Calcular tamanho do diretório
     */
    protected function getDirSize($directory)
    {
        $size = 0;
        
        if (is_dir($directory)) {
            foreach (glob($directory . '/*', GLOB_NOSORT) as $file) {
                if (is_file($file)) {
                    $size += filesize($file);
                } elseif (is_dir($file)) {
                    $size += $this->getDirSize($file);
                }
            }
        }
        
        return $size;
    }

    /**
     * Formatar bytes em formato legível
     */
    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}