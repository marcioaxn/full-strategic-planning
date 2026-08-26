<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Ponto de entrada de `php artisan db:seed`.
 *
 * Objetivo desta seed: deixar o banco em um estado limpo e mínimo, com apenas o
 * necessário para o Super Administrador conseguir autenticar e navegar. Nenhum
 * dado de planejamento (ciclos PEI, objetivos, indicadores, planos, riscos) é
 * criado — esses são cadastrados pela própria instituição na interface.
 *
 * Ordem das etapas (cada uma depende da anterior):
 *
 *   1. TruncarBancoSeeder        — esvazia todas as tabelas de domínio
 *   2. PerfilAcessoSeeder        — recria os 4 perfis de acesso
 *   3. OrganizacaoRaizSeeder     — recria a organização raiz
 *   4. SuperAdministradorSeeder  — cria o usuário e seus dois vínculos
 *
 * ATENÇÃO: a etapa 1 APAGA TODOS OS DADOS do banco configurado no .env
 * (a tabela `migrations` é a única preservada). Faça backup antes de executar
 * em um ambiente que já contenha dados reais.
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->command?->newLine();
        $this->command?->warn('  Esta seed APAGA todos os dados do banco antes de recriar o acesso inicial.');
        $this->command?->newLine();

        $this->call([
            TruncarBancoSeeder::class,
            PerfilAcessoSeeder::class,
            OrganizacaoRaizSeeder::class,
            SuperAdministradorSeeder::class,
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
                ['Senha', SuperAdministradorSeeder::SENHA],
                ['Perfil', 'Super Administrador'],
                ['Organização', OrganizacaoRaizSeeder::SIGLA.' — '.OrganizacaoRaizSeeder::NOME],
            ]
        );
        $this->command->warn('  Troque a senha no primeiro acesso: Perfil → Alterar Senha.');
        $this->command->newLine();
    }
}
