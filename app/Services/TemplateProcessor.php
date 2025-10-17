<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TemplateProcessor
{
    private string $templatePath;
    private string $templateContent = '';
    private array $variables = [];
    private bool $templateLoaded = false;

    /**
     * Construtor - carrega o template especificado
     */
    public function __construct(string $templatePath)
    {
        $this->templatePath = $templatePath;
        $this->loadTemplate();
    }

    /**
     * Carrega o template do disco
     */
    private function loadTemplate(): void
    {
        try {
            if (!file_exists($this->templatePath)) {
                throw new Exception("Template não encontrado: {$this->templatePath}");
            }

            $this->templateContent = file_get_contents($this->templatePath);
            
            if (empty($this->templateContent)) {
                throw new Exception("Template está vazio: {$this->templatePath}");
            }

            $this->templateLoaded = true;
            
            Log::info('Template HTML carregado com sucesso', [
                'template_path' => $this->templatePath,
                'content_size' => strlen($this->templateContent)
            ]);

        } catch (Exception $e) {
            Log::error('Erro ao carregar template HTML', [
                'template_path' => $this->templatePath,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Define uma única variável
     */
    public function setVariable(string $key, string $value): self
    {
        $this->variables[$key] = $value;
        
        Log::debug('Variável de template definida', [
            'key' => $key,
            'value' => $value
        ]);

        return $this;
    }

    /**
     * Define múltiplas variáveis de uma vez
     */
    public function setVariables(array $variables): self
    {
        foreach ($variables as $key => $value) {
            $this->setVariable($key, $value);
        }

        Log::info('Múltiplas variáveis de template definidas', [
            'variables_count' => count($variables),
            'variables' => array_keys($variables)
        ]);

        return $this;
    }

    /**
     * Valida se todas as variáveis necessárias foram definidas
     */
    public function validateVariables(): bool
    {
        if (!$this->templateLoaded) {
            Log::error('Template não foi carregado antes da validação');
            return false;
        }

        // Encontrar todas as variáveis no template (formato $VARIAVEL$)
        preg_match_all('/\$([A-Z_]+)\$/', $this->templateContent, $matches);
        $requiredVariables = array_unique($matches[1]);

        $missingVariables = [];
        foreach ($requiredVariables as $variable) {
            if (!isset($this->variables[$variable])) {
                $missingVariables[] = $variable;
            }
        }

        if (!empty($missingVariables)) {
            Log::error('Variáveis obrigatórias não definidas', [
                'missing_variables' => $missingVariables,
                'required_variables' => $requiredVariables,
                'defined_variables' => array_keys($this->variables)
            ]);
            return false;
        }

        Log::info('Validação de variáveis bem-sucedida', [
            'required_variables' => $requiredVariables,
            'all_defined' => true
        ]);

        return true;
    }

    /**
     * Renderiza o template substituindo as variáveis
     */
    public function render(): string
    {
        if (!$this->templateLoaded) {
            throw new Exception('Template não foi carregado');
        }

        // Validar variáveis antes de renderizar
        if (!$this->validateVariables()) {
            throw new Exception('Nem todas as variáveis obrigatórias foram definidas');
        }

        $renderedContent = $this->templateContent;

        // Substituir cada variável
        foreach ($this->variables as $key => $value) {
            $placeholder = "\${$key}\$";
            $renderedContent = str_replace($placeholder, $value, $renderedContent);
            
            Log::debug('Variável substituída no template', [
                'placeholder' => $placeholder,
                'value' => $value
            ]);
        }

        // Verificar se restaram variáveis não substituídas
        preg_match_all('/\$([A-Z_]+)\$/', $renderedContent, $unresolved);
        if (!empty($unresolved[1])) {
            Log::warning('Variáveis não substituídas encontradas no HTML final', [
                'unresolved_variables' => array_unique($unresolved[1])
            ]);
        }

        Log::info('Template renderizado com sucesso', [
            'original_size' => strlen($this->templateContent),
            'rendered_size' => strlen($renderedContent),
            'variables_substituted' => count($this->variables)
        ]);

        return $renderedContent;
    }

    /**
     * Salva o HTML renderizado em um arquivo temporário
     */
    public function saveToTempFile(string $filename = null): string
    {
        $renderedContent = $this->render();
        
        // Gerar nome único se não fornecido
        if (!$filename) {
            $filename = 'documento-' . uniqid() . '.html';
        }

        $tempPath = storage_path('app/temp/' . $filename);
        
        // Garantir que o diretório temp existe
        $tempDir = dirname($tempPath);
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $bytesWritten = file_put_contents($tempPath, $renderedContent);

        if ($bytesWritten === false) {
            throw new Exception("Erro ao salvar arquivo temporário: {$tempPath}");
        }

        Log::info('Arquivo HTML temporário criado', [
            'temp_path' => $tempPath,
            'file_size' => $bytesWritten,
            'content_preview' => substr($renderedContent, 0, 200) . '...'
        ]);

        return $tempPath;
    }

    /**
     * Deleta um arquivo temporário
     */
    public function deleteTempFile(string $filePath): bool
    {
        if (file_exists($filePath)) {
            $deleted = unlink($filePath);
            
            Log::info('Arquivo temporário deletado', [
                'file_path' => $filePath,
                'success' => $deleted
            ]);

            return $deleted;
        }

        Log::warning('Tentativa de deletar arquivo inexistente', [
            'file_path' => $filePath
        ]);

        return false;
    }

    /**
     * Limpa arquivos temporários antigos (mais de 1 hora)
     */
    public function cleanupOldTempFiles(): int
    {
        $tempDir = storage_path('app/temp');
        
        if (!is_dir($tempDir)) {
            return 0;
        }

        $deleted = 0;
        $files = glob($tempDir . '/*.html');
        $cutoffTime = time() - 3600; // 1 hora atrás

        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime) {
                if (unlink($file)) {
                    $deleted++;
                }
            }
        }

        if ($deleted > 0) {
            Log::info('Limpeza de arquivos temporários concluída', [
                'files_deleted' => $deleted,
                'temp_dir' => $tempDir
            ]);
        }

        return $deleted;
    }

    /**
     * Retorna informações sobre o template carregado
     */
    public function getTemplateInfo(): array
    {
        preg_match_all('/\$([A-Z_]+)\$/', $this->templateContent, $matches);
        $templateVariables = array_unique($matches[1]);

        return [
            'template_path' => $this->templatePath,
            'template_loaded' => $this->templateLoaded,
            'content_size' => strlen($this->templateContent),
            'template_variables' => $templateVariables,
            'defined_variables' => array_keys($this->variables),
            'missing_variables' => array_diff($templateVariables, array_keys($this->variables))
        ];
    }
}