# Documentação das Migrations

Este arquivo contém todas as migrations do projeto, ordenadas cronologicamente. Cada seção apresenta o código da migration e uma breve descrição de sua finalidade.

---

## 2014_08_09_230616_create_organizacaos_table.php
**Tabela:** `tab_organizacoes`
**Descrição:** Cria a tabela responsável pelo cadastro das organizações (unidades) do sistema, utilizando UUID como chave primária. Inclui auto-relacionamento (hierarquia) e inserção da unidade central.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateOrganizacaosTable extends Migration
{
    
    public function up()
    {
        Schema::create('tab_organizacoes', function (Blueprint $table) {
            $table->uuid('cod_organizacao')->primary();
            $table->string('sgl_organizacao')->nullable(false);
            $table->text('nom_organizacao')->nullable(false);
            $table->uuid('rel_cod_organizacao')->nullable(true);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::select("INSERT INTO tab_organizacoes (cod_organizacao, sgl_organizacao, nom_organizacao, rel_cod_organizacao, deleted_at, created_at, updated_at) VALUES ('3834910f-66f7-46d8-9104-2904d59e1241', 'UnidCent', 'Unidade Central', '3834910f-66f7-46d8-9104-2904d59e1241', NULL, '2021-10-21 10:38:09', '2021-10-21 13:20:45');");
    }

    public function down()
    {
        Schema::dropIfExists('tab_organizacoes');
    }
}
```

---

## 2014_10_11_080128_create_tab_perfil_acesso_table.php
**Tabela:** `tab_perfil_acesso`
**Descrição:** Define os perfis de acesso disponíveis no sistema (Super Administrador, Administrador da Unidade, Gestor Responsável, Gestor Substituto), com suas respectivas descrições e permissões.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateTabPerfilAcessoTable extends Migration
{
    
    public function up()
    {
        Schema::create('tab_perfil_acesso', function (Blueprint $table) {
            $table->uuid('cod_perfil')->primary();
            $table->text('dsc_perfil')->nullable(false);
            $table->text('dsc_permissao')->nullable(false);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::select("INSERT INTO tab_perfil_acesso (cod_perfil, dsc_perfil, dsc_permissao, deleted_at, created_at, updated_at) VALUES ('c00b9ebc-7014-4d37-97dc-7875e55fff2a', 'Super Administrador', 'Servidor(a) com todos os privilégios de administração do sistema', NULL, '2021-11-14 23:21:21', '2021-11-14 23:21:21');");

        DB::select("INSERT INTO tab_perfil_acesso (cod_perfil, dsc_perfil, dsc_permissao, deleted_at, created_at, updated_at) VALUES ('c00b9ebc-7014-4d37-97dc-7875e55fff3b', 'Administrador da Unidade', 'Servidor(a) com todos os privilégios de administração do sistema somente dentro da Unidade que está cadastrado', NULL, '2021-11-14 23:21:21', '2021-11-14 23:21:21');");

        DB::select("INSERT INTO tab_perfil_acesso (cod_perfil, dsc_perfil, dsc_permissao, deleted_at, created_at, updated_at) VALUES ('c00b9ebc-7014-4d37-97dc-7875e55fff4c', 'Gestor(a) Responsável', 'Servidor(a) que tem como responsabilidade manter a atualização do Plano de Ação ao qual está como responsável', NULL, '2021-11-14 23:21:21', '2021-11-14 23:21:21');");

        DB::select("INSERT INTO tab_perfil_acesso (cod_perfil, dsc_perfil, dsc_permissao, deleted_at, created_at, updated_at) VALUES ('c00b9ebc-7014-4d37-97dc-7875e55fff5d', 'Gestor(a) Substituto(a)', 'Servidor(a) que tem como responsabilidade manter a atualização do Plano de Ação ao qual está como substituto(a)', NULL, '2021-11-14 23:21:21', '2021-11-14 23:21:21');");

    }

    public function down()
    {
        Schema::dropIfExists('tab_perfil_acesso');
    }
}
```

---

## 2014_10_12_000000_create_users_table.php
**Tabela:** `users`
**Descrição:** Cria a tabela de usuários do sistema, adaptada para usar UUID como chave primária. Inclui inserção de um usuário administrador inicial.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateUsersTable extends Migration
{

    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->smallInteger('ativo')->default(1);
            $table->smallInteger('adm')->default(2);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->smallInteger('trocarsenha')->default(1);
            $table->rememberToken();
            $table->foreignId('current_team_id')->nullable();
            $table->string('profile_photo_path', 2048)->nullable();
            $table->timestamps();
        });

        DB::insert("INSERT INTO users (id, name, email, ativo, adm, password, trocarsenha, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", ['1b9839fd-464e-45cd-8700-0b964a92e358', 'Administrador', 'adm@adm.gov.br', 1, 1, '$2y$10$QQE9IiUy3dHow7ziNDVglejwhoyS2vp1llsuV9Po4LYnzBiW4szyS', 0, '2024-09-25 23:23:21', '2024-09-25 23:23:21']);
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}
```

---

## 2014_10_12_100000_create_password_resets_table.php
**Tabela:** `password_resets`
**Descrição:** Tabela padrão do Laravel para gerenciar tokens de redefinição de senha.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePasswordResetsTable extends Migration
{
    
    public function up()
    {
        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('password_resets');
    }
}
```

---

## 2014_10_12_200000_add_two_factor_columns_to_users_table.php
**Alteração:** `users`
**Descrição:** Adiciona colunas para suporte a autenticação de dois fatores (2FA) na tabela de usuários (Laravel Fortify/Jetstream).

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTwoFactorColumnsToUsersTable extends Migration
{
    
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('two_factor_secret')
                    ->after('password')
                    ->nullable();

            $table->text('two_factor_recovery_codes')
                    ->after('two_factor_secret')
                    ->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('two_factor_secret', 'two_factor_recovery_codes');
        });
    }
}
```

---

## 2014_10_13_224252_create_rel_users_tab_organizacoes_table.php
**Tabela:** `rel_users_tab_organizacoes`
**Descrição:** Cria tabela de relacionamento entre usuários e organizações (muitos-para-muitos ou um-para-muitos), vinculando `user_id` a `cod_organizacao`.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRelUsersTabOrganizacoesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rel_users_tab_organizacoes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->references('id')->on('users');
            $table->foreignUuid('cod_organizacao')->references('cod_organizacao')->on('tab_organizacoes');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rel_users_tab_organizacoes');
    }
}
```

---

## 2019_08_19_000000_create_failed_jobs_table.php
**Tabela:** `failed_jobs`
**Descrição:** Tabela padrão do Laravel para registrar falhas em filas de tarefas (Jobs).

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFailedJobsTable extends Migration
{
    
    public function up()
    {
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('failed_jobs');
    }
}
```

---

## 2019_12_14_000001_create_personal_access_tokens_table.php
**Tabela:** `personal_access_tokens`
**Descrição:** Tabela padrão do Laravel Sanctum para gerenciamento de tokens de API.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonalAccessTokensTable extends Migration
{
    
    public function up()
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('personal_access_tokens');
    }
}
```

---

## 2021_09_20_230616_create_rel_organizacao_table.php
**Tabela:** `rel_organizacao`
**Descrição:** Estabelece relacionamentos hierárquicos ou associativos adicionais entre organizações (`cod_organizacao` e `rel_cod_organizacao`).

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRelOrganizacaoTable extends Migration
{

    public function up()
    {
        Schema::create('rel_organizacao', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cod_organizacao')->references('cod_organizacao')->on('tab_organizacoes');
            $table->foreignUuid('rel_cod_organizacao')->references('cod_organizacao')->on('tab_organizacoes');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rel_organizacao');
    }
}
```

---

## 2021_10_06_140542_create_sessions_table.php
**Tabela:** `sessions`
**Descrição:** Tabela para armazenar sessões de usuários quando o driver de sessão é configurado como 'database'.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            // $table->foreignId('user_id')->nullable()->index();
            $table->uuid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sessions');
    }
}
```

---

## 2021_10_20_230616_create_acoes_table.php
**Tabela:** `acoes`
**Descrição:** Tabela de log genérica ou auditoria simplificada para registrar ações realizadas pelos usuários em determinadas tabelas.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAcoesTable extends Migration
{
    public function up()
    {
        Schema::create('acoes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('table_id')->nullable(false);
            $table->foreignUuid('user_id')->references('id')->on('users');
            $table->string('table')->nullable(false);
            $table->text('acao')->nullable(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('acoes');
    }
}
```

---

## 2021_10_31_171917_create_tab_pei_table.php
**Tabela:** `tab_pei`
**Descrição:** Cria a tabela principal do Planejamento Estratégico Institucional (PEI), definindo o ciclo do planejamento (ano início e fim).

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTabPeiTable extends Migration
{

    public function up()
    {
        Schema::create('tab_pei', function (Blueprint $table) {
            $table->uuid('cod_pei')->primary();
            $table->text('dsc_pei')->nullable(false);
            $table->smallInteger('num_ano_inicio_pei')->nullable(false);
            $table->smallInteger('num_ano_fim_pei')->nullable(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tab_pei');
    }
}
```

---

## 2021_11_01_212118_create_tab_missao_table.php
**Tabela:** `tab_missao_visao_valores`
**Descrição:** Armazena a Missão, Visão e (originalmente) Valores da organização para um determinado 

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTabMissaoTable extends Migration
{
    public function up()
    {
        Schema::create('tab_missao_visao_valores', function (Blueprint $table) {
            $table->uuid('cod_missao_visao_valores')->primary();
            $table->text('dsc_missao')->nullable(false);
            $table->text('dsc_visao')->nullable(false);
            $table->foreignUuid('cod_pei')->references('cod_pei')->on('tab_pei');
            $table->foreignUuid('cod_organizacao')->references('cod_organizacao')->on('tab_organizacoes');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tab_missao_visao_valores');
    }
}
```

---

## 2021_11_08_185623_create_tab_perspectiva_table.php
**Tabela:** `tab_perspectiva`
**Descrição:** Define as perspectivas do Balanced Scorecard (BSC) ou metodologia similar para o 

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTabPerspectivaTable extends Migration
{
    
    public function up()
    {
        Schema::create('tab_perspectiva', function (Blueprint $table) {
            $table->uuid('cod_perspectiva')->primary();
            $table->text('dsc_perspectiva')->nullable(false);
            $table->smallInteger('num_nivel_hierarquico_apresentacao')->nullable(false);
            $table->foreignUuid('cod_pei')->references('cod_pei')->on('tab_pei');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tab_perspectiva');
    }
}
```

---

## 2021_11_09_094804_create_tab_objetivo_estrategico_table.php
**Tabela:** `tab_objetivo_estrategico`
**Descrição:** Define os objetivos estratégicos associados a uma perspectiva do 

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTabObjetivoEstrategicoTable extends Migration
{

    public function up()
    {
        Schema::create('tab_objetivo_estrategico', function (Blueprint $table) {
            $table->uuid('cod_objetivo_estrategico')->primary();
            $table->text('nom_objetivo_estrategico')->nullable(false);
            $table->text('dsc_objetivo_estrategico')->nullable(false);
            $table->smallInteger('num_nivel_hierarquico_apresentacao')->nullable(false);
            $table->foreignUuid('cod_perspectiva')->references('cod_perspectiva')->on('tab_perspectiva');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tab_objetivo_estrategico');
    }
}
```

---

## 2021_11_09_095359_create_tab_nivel_hierarquico_table.php
**Tabela:** `tab_nivel_hierarquico`
**Descrição:** Tabela auxiliar para armazenar níveis hierárquicos de apresentação. Popula a tabela com 100 níveis iniciais.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateTabNivelHierarquicoTable extends Migration
{
    public function up()
    {
        Schema::create('tab_nivel_hierarquico', function (Blueprint $table) {
            $table->smallInteger('num_nivel_hierarquico_apresentacao')->primary();
            $table->timestamps();
            $table->softDeletes();
        });

        for($cont=1;$cont<=100;$cont++) {

            DB::select("INSERT INTO tab_nivel_hierarquico (num_nivel_hierarquico_apresentacao, deleted_at, created_at, updated_at) VALUES ($cont, NULL, '2021-11-09 09:59:21', '2021-11-09 09:59:21');");

        }
    }

    public function down()
    {
        Schema::dropIfExists('tab_nivel_hierarquico');
    }
}
```

---

## 2021_11_14_221355_create_tab_tipo_execucao_table.php
**Tabela:** `tab_tipo_execucao`
**Descrição:** Classifica os tipos de execução para os planos de ação (ex: Ação, Iniciativa, Projeto).

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateTabTipoExecucaoTable extends Migration
{
    
    public function up()
    {
        Schema::create('tab_tipo_execucao', function (Blueprint $table) {
            $table->uuid('cod_tipo_execucao')->primary();
            $table->string('dsc_tipo_execucao')->nullable(false);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::select("INSERT INTO tab_tipo_execucao (cod_tipo_execucao, dsc_tipo_execucao, deleted_at, created_at, updated_at) VALUES ('c00b9ebc-7014-4d37-97dc-7875e55fff1b', 'Ação', NULL, '2021-11-14 23:21:21', '2021-11-14 23:21:21');");

        DB::select("INSERT INTO tab_tipo_execucao (cod_tipo_execucao, dsc_tipo_execucao, deleted_at, created_at, updated_at) VALUES ('ecef6a50-c010-4cda-afc3-cbda245b55b0', 'Iniciativa', NULL, '2021-11-14 23:21:21', '2021-11-14 23:21:21');");

        DB::select("INSERT INTO tab_tipo_execucao (cod_tipo_execucao, dsc_tipo_execucao, deleted_at, created_at, updated_at) VALUES ('57518c30-3bc5-4305-a998-8ce8b11550ed', 'Projeto', NULL, '2021-11-14 23:21:21', '2021-11-14 23:21:21');");

    }

    public function down()
    {
        Schema::dropIfExists('tab_tipo_execucao');
    }
}
```

---

## 2021_11_14_221613_create_tab_plano_de_acao_table.php
**Tabela:** `tab_plano_de_acao`
**Descrição:** Cria a tabela de planos de ação, vinculando objetivos estratégicos, tipos de execução e organizações. Contém datas, orçamentos e status.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTabPlanoDeAcaoTable extends Migration
{
    
    public function up()
    {
        Schema::create('tab_plano_de_acao', function (Blueprint $table) {
            $table->uuid('cod_plano_de_acao')->primary();
            $table->foreignUuid('cod_objetivo_estrategico')->references('cod_objetivo_estrategico')->on('tab_objetivo_estrategico');
            $table->foreignUuid('cod_tipo_execucao')->references('cod_tipo_execucao')->on('tab_tipo_execucao');
            $table->foreignUuid('cod_organizacao')->references('cod_organizacao')->on('tab_organizacoes');
            $table->smallInteger('num_nivel_hierarquico_apresentacao')->nullable(false);
            $table->text('dsc_plano_de_acao')->nullable(false);
            $table->text('txt_principais_entregas')->nullable(true);
            $table->date('dte_inicio')->nullable(false);
            $table->date('dte_fim')->nullable(false);
            $table->decimal('vlr_orcamento_previsto', $precision = 1000, $scale = 2)->nullable(true);
            $table->string('bln_status')->nullable(false);
            $table->string('cod_ppa')->nullable(true);
            $table->string('cod_loa')->nullable(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tab_plano_de_acao');
    }
}
```

---

## 2021_11_25_081914_create_rel_users_tab_organizacoes_tab_perfil_acesso_table.php
**Tabela:** `rel_users_tab_organizacoes_tab_perfil_acesso`
**Descrição:** Tabela de relacionamento complexo para definir controle de acesso: qual usuário, em qual organização, para qual plano de ação e com qual perfil de acesso.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateRelUsersTabOrganizacoesTabPerfilAcessoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rel_users_tab_organizacoes_tab_perfil_acesso', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->references('id')->on('users');
            $table->foreignUuid('cod_organizacao')->references('cod_organizacao')->on('tab_organizacoes');
            $table->foreignUuid('cod_plano_de_acao')->references('cod_plano_de_acao')->on('tab_plano_de_acao');
            $table->foreignUuid('cod_perfil')->references('cod_perfil')->on('tab_perfil_acesso');
            $table->timestamps();
            $table->softDeletes();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rel_users_tab_organizacoes_tab_perfil_acesso');
    }
}
```

---

## 2021_12_28_232711_create_tab_indicador_table.php
**Tabela:** `tab_indicador`
**Descrição:** Cria tabela de indicadores, que podem ser vinculados a planos de ação ou objetivos estratégicos. Define atributos de medição, fórmula, fonte, etc.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTabIndicadorTable extends Migration
{

    public function up()
    {
        Schema::create('tab_indicador', function (Blueprint $table) {
            $table->uuid('cod_indicador')->primary();
            $table->foreignUuid('cod_plano_de_acao')->nullable()->references('cod_plano_de_acao')->on('tab_plano_de_acao');
            $table->foreignUuid('cod_objetivo_estrategico')->nullable()->references('cod_objetivo_estrategico')->on('tab_objetivo_estrategico');
            $table->text('dsc_tipo')->nullable(false);
            $table->text('nom_indicador')->nullable(false);
            $table->text('dsc_indicador')->nullable(false);
            $table->text('txt_observacao')->nullable(true);
            $table->text('dsc_meta')->nullable(true);
            $table->text('dsc_atributos')->nullable(true);
            $table->text('dsc_referencial_comparativo')->nullable(true);
            $table->text('dsc_unidade_medida')->nullable(false);
            $table->smallInteger('num_peso')->nullable(true);
            $table->string('bln_acumulado')->nullable(false);
            $table->text('dsc_formula')->nullable(true);
            $table->string('dsc_fonte')->nullable(true);
            $table->string('dsc_periodo_medicao')->nullable(false);
            $table->timestamps();
            $table->softDeletes();

            // Verificação para garantir que pelo menos um dos relacionamentos seja preenchido
            $table->unique(['cod_plano_de_acao', 'cod_objetivo_estrategico']);
        });
    }


    public function down()
    {
        Schema::dropIfExists('tab_indicador');
    }
}
```

---

## 2021_12_28_234715_create_tab_evolucao_indicador_table.php
**Tabela:** `tab_evolucao_indicador`
**Descrição:** Registra a evolução dos indicadores ao longo do tempo (mensalmente), comparando valores previstos vs realizados.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTabEvolucaoIndicadorTable extends Migration
{
    
    public function up()
    {
        Schema::create('tab_evolucao_indicador', function (Blueprint $table) {
            $table->uuid('cod_evolucao_indicador')->primary();
            $table->foreignUuid('cod_indicador')->references('cod_indicador')->on('tab_indicador');
            $table->smallInteger('num_ano')->nullable(false);
            $table->smallInteger('num_mes')->nullable(false);
            $table->decimal('vlr_previsto', $precision = 1000, $scale = 2)->nullable(true);
            $table->decimal('vlr_realizado', $precision = 1000, $scale = 2)->nullable(true);
            $table->text('txt_avaliacao')->nullable(true);
            $table->string('bln_atualizado')->nullable(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tab_evolucao_indicador');
    }
}
```

---

## 2021_12_28_235603_create_tab_linha_base_indicador_table.php
**Tabela:** `tab_linha_base_indicador`
**Descrição:** Armazena a linha de base (valor inicial) de um indicador para um determinado ano.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTabLinhaBaseIndicadorTable extends Migration
{
    
    public function up()
    {
        Schema::create('tab_linha_base_indicador', function (Blueprint $table) {
            $table->uuid('cod_linha_base')->primary();
            $table->foreignUuid('cod_indicador')->references('cod_indicador')->on('tab_indicador')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->decimal('num_linha_base', $precision = 1000, $scale = 2);
            $table->smallInteger('num_ano')->nullable(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tab_linha_base_indicador');
    }
}
```

---

## 2022_01_03_105544_create_tab_meta_por_ano_table.php
**Tabela:** `tab_meta_por_ano`
**Descrição:** Define metas anuais para os indicadores.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTabMetaPorAnoTable extends Migration
{

    public function up()
    {
        Schema::create('tab_meta_por_ano', function (Blueprint $table) {
            $table->uuid('cod_meta_por_ano')->primary();
            $table->foreignUuid('cod_indicador')->references('cod_indicador')->on('tab_indicador');
            $table->smallInteger('num_ano')->nullable(false);
            $table->decimal('meta', $precision = 1000, $scale = 2)->nullable(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tab_meta_por_ano');
    }
}
```

---

## 2022_01_18_133729_create_tab_audit_table.php
**Tabela:** `tab_audit`
**Descrição:** Tabela customizada de auditoria para registrar alterações (antes/depois) em tabelas, incluindo IP e usuário responsável.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTabAuditTable extends Migration
{

    public function up()
    {
        Schema::create('tab_audit', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('acao')->nullable(false);
            $table->text('antes')->nullable(true);
            $table->text('depois')->nullable(true);
            $table->string('table')->nullable(false);
            $table->string('column_name')->nullable(false);
            $table->string('data_type')->nullable(true);
            $table->string('table_id')->nullable(false);
            $table->string('ip')->nullable(false);
            $table->foreignUuid('user_id')->references('id')->on('users');
            $table->timestamp('dte_expired_at')->nullable(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tab_audit');
    }
}
```

---

## 2022_01_26_152500_create_tab_grau_satisfcao_table.php
**Tabela:** `tab_grau_satisfcao`
**Descrição:** Configura faixas de valores (mínimo e máximo) e cores para representar graus de satisfação (Farol de desempenho).

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTabGrauSatisfcaoTable extends Migration
{
    
    public function up()
    {
        Schema::create('tab_grau_satisfcao', function (Blueprint $table) {
            $table->uuid('cod_grau_satisfcao')->primary();
            $table->text('dsc_grau_satisfcao')->nullable(false);
            $table->string('cor')->nullable(false);
            $table->decimal('vlr_minimo', $precision = 1000, $scale = 2)->nullable(false);
            $table->decimal('vlr_maximo', $precision = 1000, $scale = 2)->nullable(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tab_grau_satisfcao');
    }
}
```

---

## 2022_02_07_100332_create_tab_arquivos_table.php
**Tabela:** `tab_arquivos`
**Descrição:** Permite anexar arquivos às evoluções de indicadores (comprovantes, relatórios).

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTabArquivosTable extends Migration
{
    
    public function up()
    {
        Schema::create('tab_arquivos', function (Blueprint $table) {
            $table->uuid('cod_arquivo')->primary();
            $table->foreignUuid('cod_evolucao_indicador')->references('cod_evolucao_indicador')->on('tab_evolucao_indicador');
            $table->text('txt_assunto')->nullable(false);
            $table->text('data')->nullable(false);
            $table->text('dsc_nome_arquivo')->nullable(false);
            $table->string('dsc_tipo')->nullable(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tab_arquivos');
    }
}
```

---

## 2023_01_10_164526_create_tab_atividade_cadeia_valor_table.php
**Tabela:** `tab_atividade_cadeia_valor`
**Descrição:** Mapeamento da Cadeia de Valor, vinculando atividades às perspectivas e ao 

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTabAtividadeCadeiaValorTable extends Migration
{
    public function up()
    {
        Schema::create('tab_atividade_cadeia_valor', function (Blueprint $table) {
            $table->uuid('cod_atividade_cadeia_valor')->primary();
            $table->foreignUuid('cod_pei')->references('cod_pei')->on('tab_pei');
            $table->foreignUuid('cod_perspectiva')->references('cod_perspectiva')->on('tab_perspectiva');
            $table->text('dsc_atividade')->nullable(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tab_atividade_cadeia_valor');
    }
}
```

---

## 2023_01_11_162049_create_tab_processos_atividade_cadeia_valor_table.php
**Tabela:** `tab_processos_atividade_cadeia_valor`
**Descrição:** Detalha os processos (entradas, transformações, saídas) associados às atividades da cadeia de valor.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTabProcessosAtividadeCadeiaValorTable extends Migration
{
    public function up()
    {
        Schema::create('tab_processos_atividade_cadeia_valor', function (Blueprint $table) {
            $table->uuid('cod_processo_atividade_cadeia_valor')->primary();
            $table->foreignUuid('cod_atividade_cadeia_valor')->references('cod_atividade_cadeia_valor')->on('tab_atividade_cadeia_valor');
            $table->text('dsc_entrada')->nullable(false);
            $table->text('dsc_transformacao')->nullable(false);
            $table->text('dsc_saida')->nullable(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tab_processos_atividade_cadeia_valor');
    }
}
```

---

## 2024_06_18_114518_create_valores_table.php
**Tabela:** `tab_valores`
**Descrição:** Cria tabela específica para 'Valores' organizacionais, separando-os da tabela original de Missão/Visão.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateValoresTable extends Migration
{
    public function up()
    {
        Schema::create('tab_valores', function (Blueprint $table) {
            $table->uuid('cod_valor')->primary();
            $table->text('nom_valor')->nullable(false);
            $table->text('dsc_valor')->nullable(false);
            $table->foreignUuid('cod_pei')->references('cod_pei')->on('tab_pei');
            $table->foreignUuid('cod_organizacao')->references('cod_organizacao')->on('tab_organizacoes');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tab_valores');
    }
}
```

---

## 2024_06_21_172717_create_tab_futuro_almejado_objetivo_estrategico_table.php
**Tabela:** `tab_futuro_almejado_objetivo_estrategico`
**Descrição:** Permite definir 'Futuros Almejados' vinculados aos objetivos estratégicos.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTabFuturoAlmejadoObjetivoEstrategicoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tab_futuro_almejado_objetivo_estrategico', function (Blueprint $table) {
            $table->uuid('cod_futuro_almejado')->primary();
            $table->text('dsc_futuro_almejado')->nullable(false);
            $table->foreignUuid('cod_objetivo_estrategico')->references('cod_objetivo_estrategico')->on('tab_objetivo_estrategico');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tab_futuro_almejado_objetivo_estrategico');
    }
}
```

---

## 2024_07_01_150643_create_rel_indicador_objetivo_estrategico_organizacao.php
**Tabela:** `rel_indicador_objetivo_estrategico_organizacao`
**Descrição:** Relacionamento n-para-n entre indicadores e organizações, especificamente no contexto de objetivos estratégicos.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRelIndicadorObjetivoEstrategicoOrganizacao extends Migration
{
    public function up()
    {
        Schema::create('rel_indicador_objetivo_estrategico_organizacao', function (Blueprint $table) {
            $table->foreignUuid('cod_indicador')->references('cod_indicador')->on('tab_indicador');
            $table->foreignUuid('cod_organizacao')->references('cod_organizacao')->on('tab_organizacoes');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rel_indicador_objetivo_estrategico_organizacao');
    }
}
```

---

## 2024_11_15_215604_create_tab_entregas_table.php
**Tabela:** `tab_entregas`
**Descrição:** Cria a tabela de entregas, vinculada aos planos de ação, para gerenciar os resultados concretos esperados.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTabEntregasTable extends Migration
{
    public function up()
    {
        Schema::create('tab_entregas', function (Blueprint $table) {
            // Chave Primária UUID
            $table->uuid('cod_entrega')->primary();

            // Relacionamentos com Planos de Ação e Objetivos Estratégicos
            $table->foreignUuid('cod_plano_de_acao')
                ->nullable()
                ->references('cod_plano_de_acao')
                ->on('tab_plano_de_acao')
                ->onDelete('cascade');

            // Campos para a entrega
            $table->text('dsc_entrega');  // Descrição da entrega
            $table->string('bln_status')->nullable(false);
            $table->string('dsc_periodo_medicao')->nullable(false);

            // Timestamps e SoftDeletes
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tab_entregas');
    }
}
```

---

## 2024_11_21_193856_create_audits_table.php
**Tabela:** `audits`
**Descrição:** Configura a tabela para o pacote `owen-it/laravel-auditing`, que faz auditoria detalhada de models.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $connection = config('audit.drivers.database.connection', config('database.default'));
        $table = config('audit.drivers.database.table', 'audits');

        Schema::connection($connection)->create($table, function (Blueprint $table) {

            $morphPrefix = config('audit.user.morph_prefix', 'user');

            $table->bigIncrements('id');
            $table->uuid($morphPrefix . '_type')->nullable();
            $table->uuid($morphPrefix . '_id')->nullable();
            $table->string('event');
            $table->morphs('auditable');
            $table->text('old_values')->nullable();
            $table->text('new_values')->nullable();
            $table->text('url')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent', 1023)->nullable();
            $table->string('tags')->nullable();
            $table->timestamps();

            $table->index([$morphPrefix . '_id', $morphPrefix . '_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $connection = config('audit.drivers.database.connection', config('database.default'));
        $table = config('audit.drivers.database.table', 'audits');

        Schema::connection($connection)->drop($table);
    }
}
```

---

## 2024_11_23_104155_create_tab_status_table.php
**Tabela:** `tab_status`
**Descrição:** Cria tabela simples de domínios de status.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTabStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tab_status', function (Blueprint $table) {
            $table->uuid('cod_status')->primary();
            $table->text('dsc_status')->nullable(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tab_status');
    }
}
```

---

## 2024_11_26_085622_update_auditable_id_to_uuid_on_audits_table.php
**Alteração:** `audits`
**Descrição:** Altera a coluna `auditable_id` para UUID, permitindo auditar models que usam UUID.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateAuditableIdToUuidOnAuditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $connection = config('audit.drivers.database.connection', config('database.default'));
        $table = config('audit.drivers.database.table', 'audits');

        // Usando comando SQL para alterar o tipo com a cláusula USING
        DB::connection($connection)->statement("
            ALTER TABLE {$table}
            ALTER COLUMN auditable_id TYPE UUID USING auditable_id::text::UUID
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $connection = config('audit.drivers.database.connection', config('database.default'));
        $table = config('audit.drivers.database.table', 'audits');

        // Reverter o tipo para BIGINT
        DB::connection($connection)->statement("
            ALTER TABLE {$table}
            ALTER COLUMN auditable_id TYPE BIGINT USING auditable_id::text::BIGINT
        ");
    }
}
```

---

## 2024_11_26_092223_update_user_id_to_uuid_on_audits_table.php
**Alteração:** `audits`
**Descrição:** Altera a coluna `user_id` para UUID, para suportar a tabela de usuários que usa UUID.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateUserIdToUuidOnAuditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $connection = config('audit.drivers.database.connection', config('database.default'));
        $table = config('audit.drivers.database.table', 'audits');

        // Alterando o tipo de coluna user_id para UUID
        DB::connection($connection)->statement("
            ALTER TABLE {$table}
            ALTER COLUMN user_id TYPE UUID USING user_id::text::UUID
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $connection = config('audit.drivers.database.connection', config('database.default'));
        $table = config('audit.drivers.database.table', 'audits');

        // Revertendo para BIGINT
        DB::connection($connection)->statement("
            ALTER TABLE {$table}
            ALTER COLUMN user_id TYPE BIGINT USING user_id::text::BIGINT
        ");
    }
}
```

---

## 2024_11_26_095544_update_user_type_to_varchar_on_audits_table.php
**Alteração:** `audits`
**Descrição:** Ajusta o tipo da coluna `user_type` para `varchar(255)` para armazenar o nome da classe do model de usuário corretamente.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateUserTypeToVarcharOnAuditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $connection = config('audit.drivers.database.connection', config('database.default'));
        $table = config('audit.drivers.database.table', 'audits');

        // Alterando o tipo da coluna user_type para character varying(255)
        DB::connection($connection)->statement("
            ALTER TABLE {$table}
            ALTER COLUMN user_type TYPE character varying(255)
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $connection = config('audit.drivers.database.connection', config('database.default'));
        $table = config('audit.drivers.database.table', 'audits');

        // Revertendo para UUID
        DB::connection($connection)->statement("
            ALTER TABLE {$table}
            ALTER COLUMN user_type TYPE uuid USING user_type::uuid
        ");
    }
}
```

---

## 2024_12_02_085712_remove_txt_principais_entregas_from_tab_plano_de_acao_table.php
**Alteração:** `tab_plano_de_acao`
**Descrição:** Remove a coluna `txt_principais_entregas`, pois as entregas passaram a ser gerenciadas em uma tabela própria (`tab_entregas`).

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveTxtPrincipaisEntregasFromTabPlanoDeAcaoTable extends Migration
{
    public function up()
    {
        Schema::table('tab_plano_de_acao', function (Blueprint $table) {
            $table->dropColumn('txt_principais_entregas');
        });
    }

    public function down()
    {
        Schema::table('tab_plano_de_acao', function (Blueprint $table) {
            $table->text('txt_principais_entregas')->nullable();
        });
    }
}
```

---

## 2024_12_09_085250_add_num_nivel_hierarquico_apresentacao_to_tab_entregas_table.php
**Alteração:** `tab_entregas`
**Descrição:** Adiciona o nível hierárquico na tabela de entregas para controle de apresentação/ordenação.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNumNivelHierarquicoApresentacaoToTabEntregasTable extends Migration
{
    public function up()
    {
        Schema::table('tab_entregas', function (Blueprint $table) {
            $table->smallInteger('num_nivel_hierarquico_apresentacao')->notNullable();
        });
    }

    public function down()
    {
        Schema::table('tab_entregas', function (Blueprint $table) {
            $table->dropColumn('num_nivel_hierarquico_apresentacao');
        });
    }
}
```
