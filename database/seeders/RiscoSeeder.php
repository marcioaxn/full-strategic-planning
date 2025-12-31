<?php

namespace Database\Seeders;

use App\Models\Risco;
use App\Models\PEI\PEI;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RiscoSeeder extends Seeder
{
    public function run(): void
    {
        $peiAtivo = PEI::ativos()->first();
        if (!$peiAtivo) {
            $this->command->warn('Nenhum PEI ativo encontrado.');
            return;
        }

        $organizacoes = Organization::all();
        $usuarios = User::all();

        if ($organizacoes->isEmpty() || $usuarios->isEmpty()) {
            $this->command->warn('Organizações ou usuários não encontrados.');
            return;
        }

        // Limpar riscos existentes do PEI ativo
        DB::table('tab_risco')->where('cod_pei', $peiAtivo->cod_pei)->delete();

        $this->command->info('Criando Riscos...');

        $riscos = [];
        $categorias = ['Estratégico', 'Operacional', 'Financeiro', 'Compliance', 'Tecnológico', 'Reputacional'];
        $status = ['Identificado', 'Em Monitoramento', 'Em Mitigação', 'Controlado', 'Encerrado'];

        // Criar 20-30 riscos
        for ($i = 1; $i <= rand(20, 30); $i++) {
            $probabilidade = rand(1, 5);
            $impacto = rand(1, 5);
            $nivelRisco = $probabilidade * $impacto;

            $riscos[] = [
                'cod_pei' => $peiAtivo->cod_pei,
                'cod_organizacao' => $organizacoes->random()->cod_organizacao,
                'num_codigo_risco' => $i,
                'dsc_titulo' => $this->gerarTituloRisco($i),
                'txt_descricao' => $this->gerarDescricaoRisco(),
                'dsc_categoria' => $categorias[array_rand($categorias)],
                'dsc_status' => $nivelRisco >= 16 ? 'Em Mitigação' : $status[array_rand($status)],
                'num_probabilidade' => $probabilidade,
                'num_impacto' => $impacto,
                'num_nivel_risco' => $nivelRisco,
                'txt_causas' => $this->gerarCausas(),
                'txt_consequencias' => $this->gerarConsequencias(),
                'cod_responsavel_monitoramento' => $usuarios->random()->id,
                'created_at' => now()->subDays(rand(1, 365)),
                'updated_at' => now(),
            ];
        }

        // Inserir em lotes
        foreach (array_chunk($riscos, 50) as $chunk) {
            Risco::insert($chunk);
        }

        $this->command->info('✓ ' . count($riscos) . ' Riscos criados com sucesso!');
    }

    private function gerarTituloRisco(int $numero): string
    {
        $titulos = [
            'Descontinuidade de recursos orçamentários',
            'Rotatividade de pessoal-chave',
            'Falha em sistemas críticos de TI',
            'Mudanças na legislação aplicável',
            'Perda de competitividade institucional',
            'Inadequação de processos internos',
            'Exposição a riscos cibernéticos',
            'Resistência à mudança organizacional',
            'Dependência de fornecedores únicos',
            'Falta de capacitação técnica especializada',
            'Atraso em projetos estratégicos',
            'Não conformidade regulatória',
            'Insuficiência de infraestrutura física',
            'Perda de reputação institucional',
            'Descontinuidade de parcerias estratégicas',
            'Sobrecarga operacional das equipes',
            'Obsolescência tecnológica',
            'Vazamento de informações sensíveis',
            'Conflitos de interesse',
            'Falhas na comunicação interna',
        ];

        return $titulos[($numero - 1) % count($titulos)];
    }

    private function gerarDescricaoRisco(): string
    {
        $descricoes = [
            'Risco identificado durante análise de cenários que pode impactar significativamente o atingimento dos objetivos estratégicos se não for adequadamente mitigado.',
            'Situação de potencial impacto negativo aos resultados organizacionais, requerendo monitoramento contínuo e ações preventivas.',
            'Evento incerto que, caso ocorra, pode comprometer a execução das atividades planejadas e afetar o desempenho institucional.',
            'Vulnerabilidade identificada no ambiente organizacional que pode se materializar e causar desvios nos resultados esperados.',
        ];

        return $descricoes[array_rand($descricoes)];
    }

    private function gerarCausas(): string
    {
        $causas = [
            'Limitações orçamentárias; Processos decisórios lentos; Falta de priorização estratégica',
            'Ausência de plano de sucessão; Política salarial não competitiva; Clima organizacional desfavorável',
            'Infraestrutura tecnológica defasada; Falta de redundância de sistemas; Manutenção preventiva insuficiente',
            'Ambiente regulatório instável; Complexidade normativa; Alterações políticas',
            'Recursos humanos insuficientes; Processos não otimizados; Falta de automação',
        ];

        return $causas[array_rand($causas)];
    }

    private function gerarConsequencias(): string
    {
        $consequencias = [
            'Comprometimento do alcance das metas estabelecidas; Perda de credibilidade institucional; Desperdício de recursos',
            'Atrasos na entrega de produtos e serviços; Insatisfação das partes interessadas; Retrabalho operacional',
            'Prejuízos financeiros; Descumprimento de prazos contratuais; Impacto negativo na imagem organizacional',
            'Sanções legais; Multas e penalidades; Responsabilização de gestores',
            'Redução da eficiência operacional; Sobrecarga de equipes; Queda na qualidade dos serviços',
        ];

        return $consequencias[array_rand($consequencias)];
    }
}
