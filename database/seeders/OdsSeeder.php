<?php

namespace Database\Seeders;

use App\Models\Agenda2030\ODS;
use Illuminate\Database\Seeder;

/**
 * Popula os 17 Objetivos de Desenvolvimento Sustentável da Agenda 2030 (ONU).
 *
 * Nomes oficiais em PT-BR (tradução Nações Unidas / Brasil) e cores
 * hexadecimais do guia de identidade visual oficial dos ODS.
 *
 * Idempotente: usa updateOrCreate, pode ser re-executado com segurança.
 */
class OdsSeeder extends Seeder
{
    public function run(): void
    {
        $ods = [
            [1,  'Erradicação da Pobreza',                'Sem Pobreza',            '#e5243b', 'Acabar com a pobreza em todas as suas formas, em todos os lugares.'],
            [2,  'Fome Zero e Agricultura Sustentável',   'Fome Zero',              '#dda63a', 'Acabar com a fome, alcançar a segurança alimentar e melhoria da nutrição e promover a agricultura sustentável.'],
            [3,  'Saúde e Bem-Estar',                     'Saúde e Bem-Estar',      '#4c9f38', 'Assegurar uma vida saudável e promover o bem-estar para todos, em todas as idades.'],
            [4,  'Educação de Qualidade',                 'Educação de Qualidade',  '#c5192d', 'Assegurar a educação inclusiva e equitativa de qualidade, e promover oportunidades de aprendizagem ao longo da vida para todos.'],
            [5,  'Igualdade de Gênero',                   'Igualdade de Gênero',    '#ff3a21', 'Alcançar a igualdade de gênero e empoderar todas as mulheres e meninas.'],
            [6,  'Água Potável e Saneamento',             'Água e Saneamento',      '#26bde2', 'Assegurar a disponibilidade e gestão sustentável da água e saneamento para todos.'],
            [7,  'Energia Limpa e Acessível',             'Energia Limpa',          '#fcc30b', 'Assegurar o acesso confiável, sustentável, moderno e a preço acessível à energia para todos.'],
            [8,  'Trabalho Decente e Crescimento Econômico', 'Trabalho e Crescimento', '#a21942', 'Promover o crescimento econômico sustentado, inclusivo e sustentável, emprego pleno e produtivo e trabalho decente para todos.'],
            [9,  'Indústria, Inovação e Infraestrutura',  'Indústria e Inovação',   '#fd6925', 'Construir infraestrutura resiliente, promover a industrialização inclusiva e sustentável e fomentar a inovação.'],
            [10, 'Redução das Desigualdades',             'Redução das Desigualdades', '#dd1367', 'Reduzir as desigualdades dentro dos países e entre eles.'],
            [11, 'Cidades e Comunidades Sustentáveis',    'Cidades Sustentáveis',   '#fd9d24', 'Tornar as cidades e os assentamentos humanos inclusivos, seguros, resilientes e sustentáveis.'],
            [12, 'Consumo e Produção Responsáveis',       'Consumo Responsável',    '#bf8b2e', 'Assegurar padrões de produção e de consumo sustentáveis.'],
            [13, 'Ação Contra a Mudança Global do Clima', 'Ação Climática',         '#3f7e44', 'Tomar medidas urgentes para combater a mudança do clima e seus impactos.'],
            [14, 'Vida na Água',                          'Vida na Água',           '#0a97d9', 'Conservação e uso sustentável dos oceanos, dos mares e dos recursos marinhos para o desenvolvimento sustentável.'],
            [15, 'Vida Terrestre',                        'Vida Terrestre',         '#56c02b', 'Proteger, recuperar e promover o uso sustentável dos ecossistemas terrestres, gerir de forma sustentável as florestas, combater a desertificação, deter e reverter a degradação da terra e deter a perda de biodiversidade.'],
            [16, 'Paz, Justiça e Instituições Eficazes',  'Paz e Justiça',          '#00689d', 'Promover sociedades pacíficas e inclusivas para o desenvolvimento sustentável, proporcionar o acesso à justiça para todos e construir instituições eficazes, responsáveis e inclusivas em todos os níveis.'],
            [17, 'Parcerias e Meios de Implementação',    'Parcerias e Meios',      '#19486a', 'Fortalecer os meios de implementação e revitalizar a parceria global para o desenvolvimento sustentável.'],
            // ODS 18 — adição nacional brasileira (Igualdade Étnico-Racial)
            [18, 'Igualdade Étnico-Racial',               'Igualdade Étnico-Racial','#6c321a', 'Promover a igualdade étnico-racial, combater o racismo e a discriminação e garantir os direitos e a inclusão das populações negras, indígenas e demais grupos étnico-raciais — objetivo adicional instituído pelo Brasil.'],
        ];

        foreach ($ods as [$num, $nome, $abrev, $cor, $desc]) {
            ODS::updateOrCreate(
                ['num_ods' => $num],
                [
                    'nom_ods'           => $nome,
                    'nom_ods_abreviado' => $abrev,
                    'dsc_ods'           => $desc,
                    'cod_cor'           => $cor,
                    'nom_icone'         => 'ods-' . str_pad((string) $num, 2, '0', STR_PAD_LEFT) . '.png',
                ]
            );
        }

        $this->command?->info('18 ODS da Agenda 2030 populados com sucesso (17 ONU + ODS 18 nacional).');
    }
}
