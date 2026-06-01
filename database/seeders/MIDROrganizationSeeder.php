<?php

namespace Database\Seeders;

use App\Models\Organization;
use Illuminate\Database\Seeder;

class MIDROrganizationSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Unidade Central (MIDR) - UUID fixo da migration e Auto-referência
        $midr = Organization::updateOrCreate(
            ['cod_organizacao' => '3834910f-66f7-46d8-9104-2904d59e1241'],
            [
                'sgl_organizacao' => 'MIDR',
                'nom_organizacao' => 'Ministério da Integração e do Desenvolvimento Regional',
                'rel_cod_organizacao' => '3834910f-66f7-46d8-9104-2904d59e1241',
            ]
        );

        // 2. Secretarias Finalísticas (Dados Reais)
        $secretarias = [
            [
                'sgl' => 'SEDEC',
                'nom' => 'Secretaria Nacional de Proteção e Defesa Civil',
                'rel' => $midr->cod_organizacao,
            ],
            [
                'sgl' => 'SNSH',
                'nom' => 'Secretaria Nacional de Segurança Hídrica',
                'rel' => $midr->cod_organizacao,
            ],
            [
                'sgl' => 'SNFI',
                'nom' => 'Secretaria Nacional de Fundos e Instrumentos Financeiros',
                'rel' => $midr->cod_organizacao,
            ],
            [
                'sgl' => 'SDR',
                'nom' => 'Secretaria Nacional de Desenvolvimento Regional e Territorial',
                'rel' => $midr->cod_organizacao,
            ],
            [
                'sgl' => 'SE',
                'nom' => 'Secretaria Executiva',
                'rel' => $midr->cod_organizacao,
            ],
        ];

        foreach ($secretarias as $s) {
            Organization::updateOrCreate(
                ['sgl_organizacao' => $s['sgl']],
                [
                    'nom_organizacao' => $s['nom'],
                    'rel_cod_organizacao' => $s['rel'],
                ]
            );
        }
    }
}
