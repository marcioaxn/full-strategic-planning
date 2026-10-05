<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Os 18 Objetivos de Desenvolvimento Sustentável: os 17 da Agenda 2030 da ONU e o
 * ODS 18 brasileiro (Igualdade Étnico-Racial).
 *
 * 🔴 Nenhum código preenchia strategic_planning.tab_ods: numa instalação nova a
 * tabela ficava vazia e a Agenda 2030 (painel, aderência do ciclo e vínculo de
 * ODS nos objetivos) não tinha o que mostrar (achado em 05/10/2026 ao montar o
 * ambiente de demonstração do manual).
 *
 * Idempotente: cria o que falta e atualiza nome, descrição, cor e ícone do que já
 * existe — nunca apaga, porque objetivos e ciclos apontam para num_ods.
 * Compatível com PostgreSQL 9.3 (SELECT + INSERT/UPDATE, sem ON CONFLICT).
 */
class OdsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (self::ODS as [$numero, $nome, $abreviado, $descricao, $cor, $icone]) {
            $dados = [
                'nom_ods' => $nome,
                'nom_ods_abreviado' => $abreviado,
                'dsc_ods' => $descricao,
                'cod_cor' => $cor,
                'nom_icone' => $icone,
                'updated_at' => now(),
            ];

            $tabela = DB::table('strategic_planning.tab_ods');

            if ($tabela->where('num_ods', $numero)->exists()) {
                DB::table('strategic_planning.tab_ods')->where('num_ods', $numero)->update($dados);
            } else {
                DB::table('strategic_planning.tab_ods')->insert($dados + ['num_ods' => $numero, 'created_at' => now()]);
            }
        }
    }

    /** @var list<array{int, string, string, string, string, string}> */
    public const ODS = [
        [1, 'Erradicação da Pobreza', 'Sem Pobreza', 'Acabar com a pobreza em todas as suas formas, em todos os lugares.', '#e5243b', 'ods-01.png'],
        [2, 'Fome Zero e Agricultura Sustentável', 'Fome Zero', 'Acabar com a fome, alcançar a segurança alimentar e melhoria da nutrição e promover a agricultura sustentável.', '#dda63a', 'ods-02.png'],
        [3, 'Saúde e Bem-Estar', 'Saúde e Bem-Estar', 'Assegurar uma vida saudável e promover o bem-estar para todos, em todas as idades.', '#4c9f38', 'ods-03.png'],
        [4, 'Educação de Qualidade', 'Educação de Qualidade', 'Assegurar a educação inclusiva e equitativa de qualidade, e promover oportunidades de aprendizagem ao longo da vida para todos.', '#c5192d', 'ods-04.png'],
        [5, 'Igualdade de Gênero', 'Igualdade de Gênero', 'Alcançar a igualdade de gênero e empoderar todas as mulheres e meninas.', '#ff3a21', 'ods-05.png'],
        [6, 'Água Potável e Saneamento', 'Água e Saneamento', 'Assegurar a disponibilidade e gestão sustentável da água e saneamento para todos.', '#26bde2', 'ods-06.png'],
        [7, 'Energia Limpa e Acessível', 'Energia Limpa', 'Assegurar o acesso confiável, sustentável, moderno e a preço acessível à energia para todos.', '#fcc30b', 'ods-07.png'],
        [8, 'Trabalho Decente e Crescimento Econômico', 'Trabalho e Crescimento', 'Promover o crescimento econômico sustentado, inclusivo e sustentável, emprego pleno e produtivo e trabalho decente para todos.', '#a21942', 'ods-08.png'],
        [9, 'Indústria, Inovação e Infraestrutura', 'Indústria e Inovação', 'Construir infraestrutura resiliente, promover a industrialização inclusiva e sustentável e fomentar a inovação.', '#fd6925', 'ods-09.png'],
        [10, 'Redução das Desigualdades', 'Redução das Desigualdades', 'Reduzir as desigualdades dentro dos países e entre eles.', '#dd1367', 'ods-10.png'],
        [11, 'Cidades e Comunidades Sustentáveis', 'Cidades Sustentáveis', 'Tornar as cidades e os assentamentos humanos inclusivos, seguros, resilientes e sustentáveis.', '#fd9d24', 'ods-11.png'],
        [12, 'Consumo e Produção Responsáveis', 'Consumo Responsável', 'Assegurar padrões de produção e de consumo sustentáveis.', '#bf8b2e', 'ods-12.png'],
        [13, 'Ação Contra a Mudança Global do Clima', 'Ação Climática', 'Tomar medidas urgentes para combater a mudança do clima e seus impactos.', '#3f7e44', 'ods-13.png'],
        [14, 'Vida na Água', 'Vida na Água', 'Conservação e uso sustentável dos oceanos, dos mares e dos recursos marinhos para o desenvolvimento sustentável.', '#0a97d9', 'ods-14.png'],
        [15, 'Vida Terrestre', 'Vida Terrestre', 'Proteger, recuperar e promover o uso sustentável dos ecossistemas terrestres, gerir de forma sustentável as florestas, combater a desertificação, deter e reverter a degradação da terra e deter a perda de biodiversidade.', '#56c02b', 'ods-15.png'],
        [16, 'Paz, Justiça e Instituições Eficazes', 'Paz e Justiça', 'Promover sociedades pacíficas e inclusivas para o desenvolvimento sustentável, proporcionar o acesso à justiça para todos e construir instituições eficazes, responsáveis e inclusivas em todos os níveis.', '#00689d', 'ods-16.png'],
        [17, 'Parcerias e Meios de Implementação', 'Parcerias e Meios', 'Fortalecer os meios de implementação e revitalizar a parceria global para o desenvolvimento sustentável.', '#19486a', 'ods-17.png'],
        [18, 'Igualdade Étnico-Racial', 'Igualdade Étnico-Racial', 'Promover a igualdade étnico-racial, combater o racismo e a discriminação e garantir os direitos e a inclusão das populações negras, indígenas e demais grupos étnico-raciais — objetivo adicional instituído pelo Brasil.', '#6c321a', 'ods-18.png'],
    ];
}
