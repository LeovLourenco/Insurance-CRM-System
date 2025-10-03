<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CadastroCorretor;
use Carbon\Carbon;

class CadastroCorretorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $cadastros = [
            [
                'data_hora' => Carbon::now()->subDays(5),
                'corretora' => 'Corretora ABC Seguros Ltda',
                'cnpj' => '12345678000195',
                'email' => 'contato@abc-seguros.com.br',
                'responsavel' => 'João Silva Santos',
                'telefone' => '11987654321',
                'seguradoras' => 'Bradesco, Itaú, Porto Seguro',
                'tipo' => 'Corretor Pessoa Jurídica'
            ],
            [
                'data_hora' => Carbon::now()->subDays(3),
                'corretora' => 'Maria Fernanda Corretora',
                'cnpj' => '98765432000187',
                'email' => 'maria@mfcorretora.com.br',
                'responsavel' => 'Maria Fernanda Oliveira',
                'telefone' => '21999887766',
                'seguradoras' => 'Allianz, Liberty, HDI',
                'tipo' => 'Corretor Pessoa Física'
            ],
            [
                'data_hora' => Carbon::now()->subDays(2),
                'corretora' => 'Segura Mais Corretagem',
                'cnpj' => '11223344000156',
                'email' => 'vendas@seguramais.com.br',
                'responsavel' => 'Carlos Eduardo Pereira',
                'telefone' => '11955443322',
                'seguradoras' => 'Zurich, SulAmérica, Mapfre',
                'tipo' => 'Corretor Pessoa Jurídica'
            ],
            [
                'data_hora' => Carbon::now()->subDay(),
                'corretora' => 'Pedro Henrique Corretor Individual',
                'cnpj' => null,
                'email' => 'pedro.henrique@gmail.com',
                'responsavel' => 'Pedro Henrique Costa',
                'telefone' => '85988776655',
                'seguradoras' => 'Bradesco, Porto Seguro',
                'tipo' => 'Corretor Pessoa Física'
            ],
            [
                'data_hora' => Carbon::now()->subHours(6),
                'corretora' => 'Proteção Total Seguros',
                'cnpj' => '55667788000199',
                'email' => 'contato@protecaototal.com.br',
                'responsavel' => 'Ana Clara Rodrigues',
                'telefone' => '31977665544',
                'seguradoras' => 'Tokio Marine, Generali, Chubb',
                'tipo' => 'Corretor Pessoa Jurídica'
            ],
            [
                'data_hora' => Carbon::now()->subHours(2),
                'corretora' => 'Roberto Silva & Associados',
                'cnpj' => '99887766000133',
                'email' => 'roberto@rsilvaassoc.com.br',
                'responsavel' => 'Roberto Silva',
                'telefone' => '47966554433',
                'seguradoras' => 'Itaú, SulAmérica, Liberty',
                'tipo' => 'Corretor Pessoa Jurídica'
            ]
        ];

        foreach ($cadastros as $cadastro) {
            CadastroCorretor::create($cadastro);
        }
    }
}
