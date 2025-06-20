<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Corretora;
use App\Models\Seguradora;
use Illuminate\Support\Facades\DB;

class CorretoraSeguradoraSeeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('app/dados/relação_corretora_seguradoras_formata.csv');

        if (!file_exists($path)) {
            echo "❌ Arquivo não encontrado: {$path}\n";
            return;
        }

        $linhas = array_map(function ($linha) {
            return str_getcsv($linha, ';');
        }, file($path));

        // Remove BOM e limpa espaços dos cabeçalhos
        $header = array_map(function ($value) {
            return trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $value));
        }, array_shift($linhas));

        foreach ($linhas as $linha) {
            $data = array_combine($header, $linha);

            if (!$data || empty($data['corretora_nome']) || empty($data['seguradora_nome'])) {
                echo "⚠️ Linha inválida: " . json_encode($linha) . "\n";
                continue;
            }

            $corretoraNome = trim($data['corretora_nome']);
            $seguradoraNome = trim($data['seguradora_nome']);

            $corretora = Corretora::where('nome', $corretoraNome)->first();
            $seguradora = Seguradora::where('nome', $seguradoraNome)->first();

            if ($corretora && $seguradora) {
                $existe = DB::table('corretora_seguradora')
                    ->where('corretora_id', $corretora->id)
                    ->where('seguradora_id', $seguradora->id)
                    ->exists();

                if (!$existe) {
                    DB::table('corretora_seguradora')->insert([
                        'corretora_id' => $corretora->id,
                        'seguradora_id' => $seguradora->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    echo "✔ Vínculo criado: {$corretoraNome} → {$seguradoraNome}\n";
                } else {
                    echo "ℹ Já existe: {$corretoraNome} → {$seguradoraNome}\n";
                }
            } else {
                echo "❌ Não encontrado: {$corretoraNome} ou {$seguradoraNome}\n";
            }
        }

        echo "✅ Importação concluída.\n";
    }
}
