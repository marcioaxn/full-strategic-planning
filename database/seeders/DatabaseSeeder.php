<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Ponto de entrada de `php artisan db:seed`.
 *
 * Objetivo desta seed: garantir o mínimo necessário para o Super Administrador
 * conseguir autenticar e navegar. Nenhum dado de planejamento (ciclos PEI,
 * objetivos, indicadores, planos, riscos) é criado — esses são cadastrados pela
 * própria instituição na interface.
 *
 * ESTA SEED NÃO APAGA NADA. As três etapas são idempotentes: cada uma verifica
 * se o registro já existe e o atualiza, ou o cria quando falta. Rodar `db:seed`
 * em um banco com dados reais é seguro e não duplica registro nenhum.
 *
 *   1. PerfilAcessoSeeder        — garante os 4 perfis de acesso
 *   2. OrganizacaoRaizSeeder     — garante a organização raiz
 *   3. SuperAdministradorSeeder  — garante o usuário e seus dois vínculos
 *   4. TipoExecucaoSeeder        — garante os tipos de Iniciativa (Ação, Projeto)
 *
 * A etapa 4 é o que faz a opção nova chegar ao cliente JÁ INSTALADO: vocabulário
 * controlado semeado dentro do up() de uma migration nunca alcança quem já rodou
 * aquela migration. Aqui, o mesmo `db:seed` serve instalação nova e antiga.
 *
 * A truncagem saiu daqui: era exigência de um cliente específico, já atendida no
 * último deploy, e mantê-la no caminho padrão do `db:seed` colocava todo banco a
 * um comando de distância de perder os dados. Para limpeza profunda deliberada,
 * use `php artisan banco:zerar-dominio`, que pede confirmação — ou, para apenas
 * as tabelas de domínio, `php artisan db:seed --class=TruncarBancoSeeder`.
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->command?->newLine();
        $this->command?->info('  Garantindo o acesso inicial. Nenhum dado existente é apagado.');
        $this->command?->newLine();

        $this->call([
            PerfilAcessoSeeder::class,
            OrganizacaoRaizSeeder::class,
            SuperAdministradorSeeder::class,
            TipoExecucaoSeeder::class,
        ]);

        $this->exibirCredenciais();
    }

    private function exibirCredenciais(): void
    {
        if ($this->command === null) {
            return;
        }

        $this->command->newLine();
        $this->command->info('  Acesso inicial criado com sucesso.');
        $this->command->table(
            ['Campo', 'Valor'],
            [
                ['E-mail', SuperAdministradorSeeder::EMAIL],
                ['Senha', SuperAdministradorSeeder::senhaInicial()],
                ['Perfil', 'Super Administrador'],
                ['Organização', OrganizacaoRaizSeeder::SIGLA.' — '.OrganizacaoRaizSeeder::NOME],
            ]
        );
        $this->command->warn('  Troque a senha no primeiro acesso: Perfil → Alterar Senha.');
        $this->command->newLine();
    }
}
