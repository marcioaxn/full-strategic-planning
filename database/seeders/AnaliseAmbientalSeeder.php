<?php

namespace Database\Seeders;

use App\Models\StrategicPlanning\AnaliseAmbiental;
use App\Models\StrategicPlanning\PEI;
use App\Models\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnaliseAmbientalSeeder extends Seeder
{
    public function run(): void
    {
        $peiAtivo = PEI::ativos()->first();
        if (!$peiAtivo) {
            $this->command->warn('Nenhum PEI ativo encontrado.');
            return;
        }

        $organizacoes = Organization::all();
        if ($organizacoes->isEmpty()) {
            $this->command->warn('Nenhuma organização encontrada.');
            return;
        }

        // Limpar análises existentes
        DB::table('tab_analise_ambiental')
            ->where('cod_pei', $peiAtivo->cod_pei)
            ->delete();

        $this->command->info('Criando Análises Ambientais (SWOT e PESTEL)...');

        $analises = [];

        // Criar análises para cada organização
        foreach ($organizacoes as $org) {
            // Análise SWOT
            $analises = array_merge($analises, $this->criarAnaliseSWOT($peiAtivo, $org));

            // Análise PESTEL
            $analises = array_merge($analises, $this->criarAnalisePESTEL($peiAtivo, $org));
        }

        // Inserir em lotes
        foreach (array_chunk($analises, 100) as $chunk) {
            AnaliseAmbiental::insert($chunk);
        }

        $this->command->info('✓ ' . count($analises) . ' Análises Ambientais (SWOT e PESTEL) criadas com sucesso!');
    }

    private function criarAnaliseSWOT(PEI $pei, Organization $org): array
    {
        $analises = [];
        $ordem = 1;

        // Forças (5-7 itens)
        $forcas = [
            'Equipe técnica altamente qualificada e experiente',
            'Infraestrutura física adequada e bem conservada',
            'Processos internos documentados e padronizados',
            'Cultura organizacional de inovação e melhoria contínua',
            'Forte relacionamento com parceiros estratégicos',
            'Reconhecimento institucional e boa reputação no setor',
            'Sistemas de TI modernos e integrados',
        ];

        foreach (array_slice($forcas, 0, rand(5, 7)) as $item) {
            $analises[] = $this->criarItem($pei, $org, 'SWOT', 'Força', $item, rand(3, 5), $ordem++);
        }

        // Fraquezas (5-7 itens)
        $fraquezas = [
            'Limitação de recursos orçamentários para investimentos',
            'Rotatividade elevada de pessoal em áreas críticas',
            'Dependência de sistemas legados em alguns processos',
            'Comunicação interna insuficiente entre departamentos',
            'Capacitação contínua de servidores abaixo do ideal',
            'Processos decisórios lentos em alguns setores',
            'Infraestrutura tecnológica parcialmente defasada',
        ];

        foreach (array_slice($fraquezas, 0, rand(5, 7)) as $item) {
            $analises[] = $this->criarItem($pei, $org, 'SWOT', 'Fraqueza', $item, rand(2, 4), $ordem++);
        }

        // Oportunidades (4-6 itens)
        $oportunidades = [
            'Expansão de programas governamentais de fomento',
            'Aumento da demanda por serviços especializados',
            'Novas tecnologias disruptivas disponíveis no mercado',
            'Possibilidade de parcerias público-privadas',
            'Tendência de transformação digital acelerada',
            'Crescente valorização da transparência e governança',
        ];

        foreach (array_slice($oportunidades, 0, rand(4, 6)) as $item) {
            $analises[] = $this->criarItem($pei, $org, 'SWOT', 'Oportunidade', $item, rand(3, 5), $ordem++);
        }

        // Ameaças (4-6 itens)
        $ameacas = [
            'Instabilidade econômica e restrições orçamentárias',
            'Mudanças frequentes na legislação setorial',
            'Aumento da complexidade regulatória e compliance',
            'Intensificação da concorrência por recursos humanos qualificados',
            'Riscos de segurança cibernética crescentes',
            'Pressão social por resultados mais rápidos',
        ];

        foreach (array_slice($ameacas, 0, rand(4, 6)) as $item) {
            $analises[] = $this->criarItem($pei, $org, 'SWOT', 'Ameaça', $item, rand(2, 4), $ordem++);
        }

        return $analises;
    }

    private function criarAnalisePESTEL(PEI $pei, Organization $org): array
    {
        $analises = [];
        $ordem = 1;

        // Político (3-4 itens)
        $politico = [
            'Estabilidade das políticas públicas no setor',
            'Apoio governamental às iniciativas de modernização',
            'Alinhamento com diretrizes nacionais de desenvolvimento',
            'Influência de grupos de interesse e lobby',
        ];

        foreach (array_slice($politico, 0, rand(3, 4)) as $item) {
            $analises[] = $this->criarItem($pei, $org, 'PESTEL', 'Político', $item, rand(2, 4), $ordem++);
        }

        // Econômico (3-4 itens)
        $economico = [
            'Conjuntura econômica nacional e impacto no orçamento',
            'Disponibilidade de recursos para investimento',
            'Pressão inflacionária e custos operacionais',
            'Oportunidades de captação de recursos externos',
        ];

        foreach (array_slice($economico, 0, rand(3, 4)) as $item) {
            $analises[] = $this->criarItem($pei, $org, 'PESTEL', 'Econômico', $item, rand(3, 5), $ordem++);
        }

        // Social (3-4 itens)
        $social = [
            'Expectativas crescentes da sociedade por transparência',
            'Demandas por inclusão digital e acessibilidade',
            'Mudanças demográficas e perfil do público-alvo',
            'Valorização da responsabilidade socioambiental',
        ];

        foreach (array_slice($social, 0, rand(3, 4)) as $item) {
            $analises[] = $this->criarItem($pei, $org, 'PESTEL', 'Social', $item, rand(3, 4), $ordem++);
        }

        // Tecnológico (3-4 itens)
        $tecnologico = [
            'Avanços em inteligência artificial e automação',
            'Proliferação de soluções em nuvem e SaaS',
            'Evolução das tecnologias de análise de dados',
            'Necessidade de atualização constante de sistemas',
        ];

        foreach (array_slice($tecnologico, 0, rand(3, 4)) as $item) {
            $analises[] = $this->criarItem($pei, $org, 'PESTEL', 'Tecnológico', $item, rand(4, 5), $ordem++);
        }

        // Ambiental (2-3 itens)
        $ambiental = [
            'Pressão por sustentabilidade e redução de impacto ambiental',
            'Requisitos de conformidade com normas ambientais',
            'Oportunidades em economia circular e eficiência energética',
        ];

        foreach (array_slice($ambiental, 0, rand(2, 3)) as $item) {
            $analises[] = $this->criarItem($pei, $org, 'PESTEL', 'Ambiental', $item, rand(2, 4), $ordem++);
        }

        // Legal (3-4 itens)
        $legal = [
            'Complexidade crescente do arcabouço regulatório',
            'Necessidade de conformidade com LGPD e proteção de dados',
            'Alterações na legislação trabalhista e previdenciária',
            'Requisitos de transparência e acesso à informação',
        ];

        foreach (array_slice($legal, 0, rand(3, 4)) as $item) {
            $analises[] = $this->criarItem($pei, $org, 'PESTEL', 'Legal', $item, rand(3, 5), $ordem++);
        }

        return $analises;
    }

    private function criarItem(PEI $pei, Organization $org, string $tipo, string $categoria, string $descricao, int $impacto, int $ordem): array
    {
        return [
            'cod_pei' => $pei->cod_pei,
            'cod_organizacao' => $org->cod_organizacao,
            'dsc_tipo_analise' => $tipo,
            'dsc_categoria' => $categoria,
            'dsc_item' => $descricao,
            'num_impacto' => $impacto,
            'txt_observacao' => 'Identificado durante diagnóstico estratégico organizacional',
            'num_ordem' => $ordem,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
