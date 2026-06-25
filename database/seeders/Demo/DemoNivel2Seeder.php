<?php

namespace Database\Seeders\Demo;

use App\Models\Organization;
use App\Models\StrategicPlanning\GrauSatisfacao;
use App\Models\StrategicPlanning\MissaoVisaoValores;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\StrategicPlanning\Valor;
use Illuminate\Database\Seeder;

/**
 * Nível 2 — Semi-preenchida (50% automático, 50% ao vivo).
 *
 * Popula: PEI, identidade estratégica, perspectivas BSC, graus de satisfação.
 * Deixa para o vivo: objetivos, PESTEL, riscos.
 */
class DemoNivel2Seeder extends Seeder
{
    public function run(): void
    {
        $org = Organization::whereNull('rel_cod_organizacao')->first();

        if (! $org) {
            $this->command->error('Nenhuma organização raiz encontrada. Verifique o cadastro de organizações.');
            return;
        }

        // 1. PEI
        $pei = PEI::create([
            'dsc_pei'           => 'PEI 2025–2028 — Planejamento Estratégico Integrado',
            'num_ano_inicio_pei' => 2025,
            'num_ano_fim_pei'   => 2028,
        ]);

        // 2. Identidade Estratégica
        MissaoVisaoValores::create([
            'cod_pei'          => $pei->cod_pei,
            'cod_organizacao'  => $org->cod_organizacao,
            'dsc_missao'       => 'Formular, coordenar e monitorar as políticas públicas de desenvolvimento regional e urbano, promovendo a redução das desigualdades territoriais e a melhoria da qualidade de vida da população brasileira.',
            'dsc_visao'        => 'Ser reconhecido como referência nacional em governança territorial e gestão estratégica, contribuindo para um Brasil mais equilibrado, sustentável e inovador até 2028.',
        ]);

        // 3. Valores
        $valores = [
            ['nom_valor' => 'Excelência',      'dsc_valor' => 'Comprometimento com a qualidade, a eficiência e a melhoria contínua dos serviços e processos institucionais.'],
            ['nom_valor' => 'Transparência',   'dsc_valor' => 'Clareza e abertura nas ações, decisões e resultados, promovendo o controle social e a confiança pública.'],
            ['nom_valor' => 'Colaboração',     'dsc_valor' => 'Atuação integrada entre equipes, parceiros e cidadãos, valorizando a diversidade e a inteligência coletiva.'],
        ];

        foreach ($valores as $v) {
            Valor::create([
                'cod_pei'         => $pei->cod_pei,
                'cod_organizacao' => $org->cod_organizacao,
                'nom_valor'       => $v['nom_valor'],
                'dsc_valor'       => $v['dsc_valor'],
            ]);
        }

        // 4. Perspectivas BSC
        $perspectivas = [
            ['dsc_perspectiva' => 'Resultados para a Sociedade',  'num_nivel' => 1, 'peso_ind' => 60, 'peso_planos' => 40],
            ['dsc_perspectiva' => 'Processos Internos',            'num_nivel' => 2, 'peso_ind' => 60, 'peso_planos' => 40],
            ['dsc_perspectiva' => 'Aprendizado e Crescimento',     'num_nivel' => 3, 'peso_ind' => 50, 'peso_planos' => 50],
            ['dsc_perspectiva' => 'Recursos e Infraestrutura',     'num_nivel' => 4, 'peso_ind' => 50, 'peso_planos' => 50],
        ];

        foreach ($perspectivas as $p) {
            Perspectiva::create([
                'cod_pei'                              => $pei->cod_pei,
                'dsc_perspectiva'                      => $p['dsc_perspectiva'],
                'num_nivel_hierarquico_apresentacao'   => $p['num_nivel'],
                'num_peso_indicadores'                 => $p['peso_ind'],
                'num_peso_planos'                      => $p['peso_planos'],
            ]);
        }

        // 5. Graus de Satisfação (farois do Mapa Estratégico)
        $graus = [
            ['dsc_grau_satisfacao' => 'Abaixo do Esperado', 'vlr_minimo' => 0,    'vlr_maximo' => 50,  'cor' => '#dc2626'],
            ['dsc_grau_satisfacao' => 'Em Desenvolvimento', 'vlr_minimo' => 50.01,'vlr_maximo' => 79.99,'cor' => '#f59e0b'],
            ['dsc_grau_satisfacao' => 'Satisfatório',       'vlr_minimo' => 80,   'vlr_maximo' => 100, 'cor' => '#16a34a'],
        ];

        foreach ($graus as $g) {
            GrauSatisfacao::create([
                'cod_pei'              => $pei->cod_pei,
                'dsc_grau_satisfacao'  => $g['dsc_grau_satisfacao'],
                'vlr_minimo'           => $g['vlr_minimo'],
                'vlr_maximo'           => $g['vlr_maximo'],
                'cor'                  => $g['cor'],
            ]);
        }
    }
}
