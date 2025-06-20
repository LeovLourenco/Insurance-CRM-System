<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Corretora;

class CorretorasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = storage_path('app/dados/corretoras.csv');

        if (!file_exists($path)) {
            echo "❌ Arquivo não encontrado em: $path\n";
            return;
        }

        // Lê e remove BOM (caso UTF-8 com BOM)
        $conteudo = file_get_contents($path);
        $conteudo = preg_replace('/^\xEF\xBB\xBF/', '', $conteudo); // Remove BOM

        // Converte linhas e separa colunas
        $linhas = array_map('str_getcsv', explode(PHP_EOL, $conteudo));
        $header = array_map('trim', array_shift($linhas));

        foreach ($linhas as $linha) {
            if (count($linha) < count($header)) continue;

            $data = array_combine($header, $linha);
            if (!$data || !isset($data['nome'])) continue;

            Corretora::firstOrCreate(
                ['nome' => trim($data['nome'])],
                [
                    'email' => $data['email'] ?? null,
                    'telefone' => $data['telefone'] ?? null
                ]
            );

            echo "✅ Corretora inserida: {$data['nome']}\n";
        }

        echo "🌱 Importação de corretoras finalizada.\n";
    }
}
