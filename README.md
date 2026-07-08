# Sistema de Planejamento Estratégico Integrado (PEI)

Plataforma web de gestão estratégica para **organizações públicas brasileiras**, construída sobre **Laravel 12 + Livewire 4**. Permite definir, executar e monitorar a estratégia institucional usando a metodologia **Balanced Scorecard (BSC)**, indicadores de desempenho (KPIs), planos de ação, entregas e gestão de riscos — alinhada ao **Guia Prático de Planejamento Estratégico Institucional (GPPEI / MGI 2025)** e à **Agenda 2030 / ODS**.

> **Referência metodológica:** `documentacao/pdf/Guia_PEI_VF.pdf`
> **Documento mestre do projeto:** `documentacao/documento-mestre-evolucao-sistema-pei.md`

---

## 📋 Sumário

- [O que o sistema entrega](#-o-que-o-sistema-entrega)
- [Agente de Inteligência Artificial](#-agente-de-inteligência-artificial)
- [Stack tecnológica](#-stack-tecnológica)
- [Requisitos de instalação](#-requisitos-de-instalação)
- [Instalação passo a passo](#-instalação-passo-a-passo)
  - [Opção A — Servidor Linux / Apache](#opção-a--servidor-linux--apache)
  - [Opção B — php artisan serve (desenvolvimento rápido)](#opção-b--php-artisan-serve-desenvolvimento-rápido)
- [Configuração do ambiente (.env)](#-configuração-do-ambiente-env)
- [Primeiro acesso e passos iniciais](#-primeiro-acesso-e-passos-iniciais)
- [Filas e relatórios agendados](#-filas-e-relatórios-agendados)
- [Arquitetura do sistema](#-arquitetura-do-sistema)
- [Segurança e Controle de Acesso (RBAC + ABAC)](#-segurança-e-controle-de-acesso-rbac--abac)
- [Desenvolvimento](#-desenvolvimento)
- [Testes e qualidade de código](#-testes-e-qualidade-de-código)
- [Solução de problemas frequentes](#-solução-de-problemas-frequentes)
- [Migração da versão anterior (v1 → v2)](#-migração-da-versão-anterior-v1--v2)
- [Documentação relacionada](#-documentação-relacionada)
- [Licença e créditos](#-licença-e-créditos)

---

## 🎯 O que o sistema entrega

O PEI cobre o **ciclo completo de planejamento estratégico institucional**, do diagnóstico à prestação de contas, com todos os módulos integrados entre si:

| Módulo | O que entrega |
|---|---|
| **Planejamento Estratégico (BSC)** | Ciclos PEI, Identidade (Missão / Visão / Valores), Perspectivas, Objetivos, Mapa Estratégico, Graus de Satisfação |
| **Análises de Ambiente** | SWOT e PESTEL com interface guiada |
| **Indicadores (KPIs)** | Indicadores com metas por ano, linha de base, evolução histórica, cálculo automático de farol (semáforo) e polaridade |
| **Planos de Ação** | Planos vinculados a objetivos estratégicos, tipos de execução, responsáveis e prazos |
| **Entregas (modelo Notion)** | Quadro Kanban, Lista, Timeline e Calendário; subtarefas hierárquicas, rótulos, comentários, anexos, histórico de alterações e múltiplos responsáveis |
| **Gestão de Riscos** | Matriz de risco 5 × 5, planos de mitigação e registro de ocorrências |
| **Agenda 2030 / ODS** | 17 Objetivos de Desenvolvimento Sustentável como eixo transversal, vínculo objetivo ↔ ODS e painel dedicado |
| **Relatórios e Dashboard** | Dashboard executivo com gráficos (Chart.js), geração de relatórios em PDF e exportações em Excel |
| **Organização** | Estrutura hierárquica de organizações com perfis de acesso granulares |
| **Auditoria** | Trilha de alterações completa (quem, o quê, quando) em todas as entidades de negócio |
| **Administração** | Configurações sistêmicas, alertas estratégicos e gestão de usuários |

### Fluxo metodológico guiado

O serviço `PeiGuidanceService` orienta o gestor em etapas sequenciais, garantindo que o ciclo PEI seja construído na ordem metodológica correta:

```
Ciclo PEI → Identidade (Missão/Visão/Valores) → Perspectivas BSC
  → Objetivos Estratégicos → Graus de Satisfação
  → Indicadores (KPIs) → Planos de Ação → Dashboard
```

---

## 🤖 Agente de Inteligência Artificial

O sistema possui um **Agente de IA integrado e totalmente opcional**. Quando configurado, o agente atua como assistente estratégico em tempo real em diversos módulos — sugerindo conteúdo, auditando qualidade e gerando análises preditivas alinhadas à metodologia do GPPEI/MGI 2025. **Sem configuração, todos os módulos funcionam normalmente** — os botões de IA ficam ocultos ou são silenciosamente ignorados.

### Comportamento sem o Agente configurado

O sistema é **resiliente por padrão**: a classe `AiServiceFactory` retorna `null` quando nenhuma credencial está configurada, e todos os componentes que usam IA verificam esse retorno antes de acionar qualquer chamada. Nenhum processo de negócio depende do agente para ser concluído.

### Onde o Agente de IA atua

| Módulo | Tela / Rota | Função da IA | Método |
|---|---|---|---|
| **Organizações** | `/organizacoes` | Sugere sigla e subunidades para uma nova organização com base no nome informado | `suggest()` |
| **Identidade Estratégica** | `/pei` | Sugere Missão, Visão e 5 Valores completos (formato JSON estruturado) | `suggest()` |
| **Perspectivas BSC** | `/pei/perspectivas` | Sugere as 4 perspectivas BSC na ordem metodológica DOWN-TOP, baseadas na Missão e Visão | `suggest()` |
| **Temas Norteadores** | (modal de criação) | Sugere 3 Temas Norteadores de alto nível para a organização | `suggest()` |
| **Objetivos Estratégicos** | `/objetivos` | (1) Audita a qualidade SMART do objetivo sendo redigido; (2) sugere 3 objetivos para a perspectiva selecionada | `analyzeSmart()` / `suggest()` |
| **Graus de Satisfação** | `/graus-satisfacao` | Sugere uma escala de 4–5 níveis (Crítico → Excelente) com cores e faixas percentuais | `suggest()` |
| **Análise SWOT** | `/pei/swot` | Sugere Forças, Fraquezas, Oportunidades e Ameaças (3 itens cada) no formato JSON | `suggest()` |
| **Análise PESTEL** | `/pei/pestel` | Sugere 2 fatores para cada uma das 6 dimensões PESTEL (JSON estruturado) | `suggest()` |
| **Gestão de Riscos** | `/riscos` | Sugere 3 riscos potenciais com título, categoria, descrição e medida de mitigação, baseados nos objetivos da organização | `suggest()` |
| **Planos de Ação** | `/planos` | Sugere nomes e justificativas de planos alinhados ao objetivo estratégico selecionado | `suggest()` |
| **Dashboard Executivo** | `/dashboard` | Gera um resumo estratégico executivo com análise dos KPIs da organização | `summarizeStrategy()` |
| **Relatórios** | `/relatorios` | Gera um "AI Minute" — resumo executivo em Markdown com pontos de atenção e sugestões | `suggest()` |
| **Geração de PDF** | (serviço interno) | Incorpora resumo estratégico e análise preditiva de tendências de indicadores no PDF gerado | `summarizeStrategy()` / `analyzeTrends()` |

### Provedores suportados

| Provedor | Autenticação | Dados para treino | Indicado para |
|---|---|---|---|
| **Google AI Studio** | API Key | Sim (plano gratuito) | Prototipagem e desenvolvimento |
| **Google Vertex AI** | Service Account JSON | Não (enterprise) | Produção em ambientes GCP |
| **Claude (Anthropic) via Vertex AI** | Service Account JSON (mesma do Vertex) | Não (enterprise) | Alternativa enterprise com modelos Claude |

> Documentação de implementação do provedor Claude via Vertex AI: [`documentacao/integracao-claude-vertex-ai.md`](documentacao/integracao-claude-vertex-ai.md)

### Arquitetura da integração

```
AiServiceFactory::make()
    ├── ai_provider = 'gemini-studio' → GeminiProvider    (API Key obrigatória)
    ├── ai_provider = 'vertex-ai'     → VertexAiProvider  (Project ID + SA JSON obrigatórios)
    └── credenciais ausentes          → null              (sistema opera sem IA)

Todos os Livewire components:
    $aiService = AiServiceFactory::make();
    if (!$aiService) return;   ← guard universal: sem credenciais, sem chamada
```

### Como configurar

1. Acesse **Configurações** (`/configuracoes`) — disponível apenas para Super Administradores
2. Escolha o **Provedor** (Google AI Studio ou Vertex AI)
3. Informe as credenciais correspondentes
4. Clique em **"Testar Comunicação agora"** para validar antes de salvar
5. Clique em **"Salvar e Ativar Agente"**

As credenciais são armazenadas com **criptografia em repouso** (`Crypt::encryptString`) na tabela `pei.system_settings`.

---

## 🛠️ Stack tecnológica

| Camada | Tecnologia |
|---|---|
| **Backend** | PHP 8.2+ · Laravel 12 · Livewire 4.0.0 · Alpine.js 3 (embutido no Livewire 4) |
| **Frontend** | Bootstrap 5.3 + Bootstrap Icons · Vite 7 · Livewire Blaze |
| **Banco de dados** | PostgreSQL 13+ (arquitetura multi-schema, 6 domínios) |
| **Autenticação** | Laravel Fortify + Jetstream + Sanctum |
| **Fila / Cache / Sessão** | Driver `database` (sem dependência de Redis ou Memcached) |
| **Testes** | Pest 4 |
| **Lint** | Laravel Pint (PSR-12) |
| **PDF** | `barryvdh/laravel-dompdf` |
| **Excel** | `maatwebsite/excel` |
| **Auditoria** | `owen-it/laravel-auditing` |
| **HTML helpers** | `spatie/laravel-html` |
| **Otimização de views** | `livewire/blaze` |

---

## 📦 Requisitos de instalação

Antes de começar, certifique-se de que o ambiente possui:

| Requisito | Versão mínima | Observação |
|---|---|---|
| **PHP** | 8.2 | Extensões obrigatórias: `pgsql`, `pdo_pgsql`, `intl`, `mbstring`, `gd`, `zip`, `xml` |
| **Composer** | 2.x | Gerenciador de dependências PHP |
| **Node.js** | 20 LTS | Para compilar os assets (CSS/JS) com Vite |
| **npm** | 10+ | Incluído com o Node.js 20 LTS |
| **PostgreSQL** | 13+ | Mínimo absoluto: 9.4 (necessário `jsonb` e `gen_random_uuid`) |
| **Servidor web** | Apache 2.4+ / Nginx | Ou `php artisan serve` para desenvolvimento local |

### Verificando os requisitos

```bash
php -v                    # deve mostrar 8.2.x ou superior
php -m | grep pgsql       # deve listar pdo_pgsql e pgsql
composer --version        # deve mostrar 2.x
node --version            # deve mostrar v20.x ou superior
psql --version            # deve mostrar 13.x ou superior
```

> **Extensões PHP ausentes?** No Ubuntu/Debian: `sudo apt install php8.2-pgsql php8.2-intl php8.2-mbstring php8.2-gd php8.2-zip php8.2-xml`. No XAMPP para Windows, habilite as extensões no `php.ini` removendo o `;` antes de `extension=pgsql` e `extension=pdo_pgsql`.

---

## 🚀 Instalação passo a passo

### Preparação do banco de dados (comum a todas as opções)

O sistema usa **múltiplos schemas** dentro de um único banco PostgreSQL. O banco precisa existir antes do primeiro `migrate`, mas **todos os schemas são criados automaticamente** pelas migrations — você não precisa criá-los manualmente.

```sql
-- Execute no psql ou em qualquer cliente PostgreSQL (pgAdmin, DBeaver, etc.)
CREATE DATABASE pei_producao;   -- escolha o nome que preferir

-- Opcional: crie um usuário dedicado (recomendado para produção)
CREATE USER pei_user WITH PASSWORD 'senha_forte_aqui';
GRANT ALL PRIVILEGES ON DATABASE pei_producao TO pei_user;
```

> **Extensão pgcrypto:** se o PostgreSQL não tiver a extensão `pgcrypto` habilitada no banco, a primeira migration a ativará automaticamente. Se o usuário do banco não tiver permissão `SUPERUSER`, execute manualmente: `CREATE EXTENSION IF NOT EXISTS pgcrypto;`

---

### Opção A — Servidor Linux / Apache

**1. Clone o repositório:**

```bash
cd /var/www
git clone <url_do_repositorio> pei
cd pei
```

**2. Instale as dependências:**

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

**3. Configure as permissões:**

```bash
chown -R www-data:www-data /var/www/pei
chmod -R 755 /var/www/pei
chmod -R 775 /var/www/pei/storage /var/www/pei/bootstrap/cache
```

**4. Configure o VirtualHost do Apache** (`/etc/apache2/sites-available/pei.conf`):

```apache
<VirtualHost *:80>
    ServerName pei.sua-organizacao.gov.br
    DocumentRoot /var/www/pei/public

    <Directory /var/www/pei/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/pei_error.log
    CustomLog ${APACHE_LOG_DIR}/pei_access.log combined
</VirtualHost>
```

```bash
a2ensite pei.conf
a2enmod rewrite
systemctl reload apache2
```

**5. Configure o `.env`:**

```bash
cp .env.example .env
nano .env
```

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://pei.sua-organizacao.gov.br
SESSION_DOMAIN=pei.sua-organizacao.gov.br

DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=pei_producao
DB_USERNAME=pei_user
DB_PASSWORD=senha_forte_aqui
```

**6. Finalize a instalação:**

```bash
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```

---

### Opção B — php artisan serve (desenvolvimento rápido)

Ideal para desenvolvimento local sem necessidade de configurar Apache ou Nginx.

```bash
git clone <url_do_repositorio> pei
cd pei

composer install
npm install

cp .env.example .env
```

Configure o `.env` com:

```dotenv
APP_URL=http://localhost:8000
SESSION_DOMAIN=localhost

DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=pei_dev
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

```bash
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

Acesse: `http://localhost:8000`

> Para desenvolvimento com hot-reload (Vite em modo dev), use `composer dev` em vez de `php artisan serve` + `npm run build`. Veja a seção [Desenvolvimento](#-desenvolvimento).

---

## ⚙️ Configuração do ambiente (.env)

As variáveis mais importantes e seus impactos:

### Aplicação

| Variável | Exemplo | Descrição |
|---|---|---|
| `APP_NAME` | `"Sistema PEI"` | Nome exibido no layout e nos e-mails |
| `APP_ENV` | `local` / `production` | Modo da aplicação. Em produção, sempre `production` |
| `APP_DEBUG` | `false` | Em produção, **sempre `false`** — evita expor stack traces |
| `APP_URL` | `https://pei.org.gov.br` | URL completa de acesso, incluindo subdiretório se houver |
| `APP_KEY` | gerada por `key:generate` | Nunca compartilhe ou versione esta chave |

### Banco de dados

| Variável | Exemplo | Descrição |
|---|---|---|
| `DB_CONNECTION` | `pgsql` | Não altere — o sistema é exclusivo para PostgreSQL |
| `DB_HOST` | `127.0.0.1` | Host do servidor PostgreSQL |
| `DB_PORT` | `5432` | Porta padrão do PostgreSQL |
| `DB_DATABASE` | `pei_producao` | Nome do banco criado na preparação |
| `DB_USERNAME` | `pei_user` | Usuário com acesso ao banco |
| `DB_PASSWORD` | `...` | Senha do usuário do banco |

### Sessão (crítico para o Livewire)

| Variável | Exemplo | Descrição |
|---|---|---|
| `SESSION_DRIVER` | `database` | Não altere — sessões ficam no banco |
| `SESSION_LIFETIME` | `120` | Minutos de inatividade antes de expirar a sessão |
| `SESSION_DOMAIN` | `pei.org.gov.br` | **Apenas o host**, sem protocolo, porta ou subdiretório. Se errado, o Livewire gera erros 419 |

> **Por que `SESSION_DOMAIN` é tão importante?** O Livewire faz requisições AJAX ao servidor. O cookie de sessão só é enviado nessas requisições se o domínio do cookie bater com o host da requisição. Um valor incorreto causa falhas silenciosas de autenticação ou erros 419 (CSRF token mismatch).

### E-mail

| Variável | Exemplo | Descrição |
|---|---|---|
| `MAIL_MAILER` | `smtp` | Driver de envio de e-mail |
| `MAIL_HOST` | `sandbox.smtp.mailtrap.io` | Para desenvolvimento, use [Mailtrap](https://mailtrap.io) |
| `MAIL_FROM_ADDRESS` | `noreply@org.gov.br` | Remetente dos e-mails do sistema |

> Para ambientes de desenvolvimento sem e-mail configurado, defina `MAIL_MAILER=log` — os e-mails serão gravados em `storage/logs/laravel.log` em vez de enviados.

---

## 🖥️ Primeiro acesso e passos iniciais

### Credenciais iniciais (criadas pelo seed)

Após `php artisan migrate --seed`, um Super Admin é criado automaticamente:

| Campo | Valor |
|---|---|
| **E-mail** | `user_adm@user_adm.com` |
| **Senha** | `1352@765@1452` |

> ⚠️ **Troque a senha imediatamente** após o primeiro acesso em qualquer ambiente que não seja sua máquina local de desenvolvimento. Acesse **Perfil → Alterar Senha**.

### O que fazer após o primeiro login

O sistema possui um **assistente de configuração guiado** (`PeiGuidanceService`) que orientará cada etapa. Mesmo assim, aqui está o roteiro recomendado:

1. **Trocar a senha padrão** — Menu de perfil no canto superior direito
2. **Configurar a organização** — `/organizacoes` — cadastre a unidade institucional antes de qualquer outro dado
3. **Iniciar o Ciclo PEI** — `/pei/ciclos` — defina o ciclo vigente (ex.: 2024–2027)
4. **Preencher a Identidade Estratégica** — `/pei` — Missão, Visão e Valores da organização
5. **Configurar as Perspectivas BSC** — `/pei/perspectivas` — as dimensões que estruturam os objetivos
6. **Cadastrar Objetivos Estratégicos** — `/objetivos` — vinculados às perspectivas
7. **Criar Indicadores** — `/indicadores` — com metas anuais para cada objetivo
8. **Criar Planos de Ação** — `/planos` — detalhando como os objetivos serão atingidos
9. **Acompanhar no Dashboard** — `/dashboard` — visão consolidada do ciclo

---

## ⚙️ Filas e relatórios agendados

O módulo de relatórios do PEI permite que o usuário **agende a geração automática de relatórios em PDF** com frequência diária, semanal ou mensal. Para que esses agendamentos sejam executados de fato, dois componentes de infraestrutura precisam estar em funcionamento no servidor: o **queue worker** e o **agendador de tarefas (scheduler)** do Laravel.

Esta seção explica cada um, como configurar e como verificar que estão funcionando.

---

### Como o sistema funciona internamente

```
Usuário agenda relatório (interface /relatorios)
    ↓
Registro salvo em pei.tab_relatorios_agendados
    (bln_ativo = true, dte_proxima_execucao = data escolhida)
    ↓
Cron do sistema chama php artisan schedule:run a cada minuto
    ↓
Laravel Scheduler executa reports:process-scheduled a cada hora
    ↓
Comando busca registros com dte_proxima_execucao <= agora
    ↓
PDF gerado → salvo em storage/app/public/relatorios/YYYY/MM/
    ↓
Registro criado em pei.tab_relatorios_gerados
    ↓
dte_proxima_execucao atualizada para a próxima recorrência
```

> **Em resumo:** sem o cron do sistema chamando `schedule:run`, nenhum relatório agendado será gerado — independente do que estiver configurado no sistema.

---

### Componente 1 — Queue Worker (processador de filas)

O sistema usa o driver de fila `database`, o que significa que os jobs ficam na tabela `pei.jobs` do PostgreSQL até serem processados. O queue worker é o processo que consome essa fila continuamente.

**Para que serve:** processar qualquer tarefa em background despachada pelo sistema (geração sob demanda, exportações pesadas, notificações, etc.).

**Verificar se está configurado no `.env`:**

```dotenv
QUEUE_CONNECTION=database   # já é o padrão — não altere
```

#### Em desenvolvimento (`composer dev`)

O `composer dev` já sobe o queue worker automaticamente junto com o servidor:

```bash
composer dev
# Inicia em paralelo: php artisan serve + php artisan queue:listen --tries=1 + npm run dev
```

#### Em produção — via Supervisor (recomendado)

O queue worker precisa rodar continuamente como um processo daemon. O **Supervisor** é o gerenciador de processos padrão para isso em servidores Linux.

**1. Instale o Supervisor:**

```bash
sudo apt install supervisor
```

**2. Crie o arquivo de configuração** (`/etc/supervisor/conf.d/pei-worker.conf`):

```ini
[program:pei-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/pei/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
directory=/var/www/pei
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/pei/storage/logs/worker.log
stopwaitsecs=3600
```

> Ajuste `/var/www/pei` para o caminho real da instalação e `www-data` para o usuário que roda o Apache/Nginx.

**3. Ative e inicie:**

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start pei-worker:*
```

**4. Verificar status:**

```bash
sudo supervisorctl status pei-worker:*
```

#### Em produção — via systemd (alternativa)

Caso prefira usar o systemd nativo do Linux sem instalar o Supervisor, crie o arquivo `/etc/systemd/system/pei-worker.service`:

```ini
[Unit]
Description=PEI Queue Worker
After=network.target

[Service]
User=www-data
WorkingDirectory=/var/www/pei
ExecStart=/usr/bin/php /var/www/pei/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl enable pei-worker
sudo systemctl start pei-worker
sudo systemctl status pei-worker
```

---

### Componente 2 — Scheduler (agendador de tarefas)

O scheduler do Laravel é responsável por disparar o comando `reports:process-scheduled` **a cada hora**, que por sua vez verifica quais relatórios estão com `dte_proxima_execucao <= agora` e os gera.

**O scheduler do Laravel precisa ser invocado pelo cron do sistema operacional a cada minuto.** Essa é a única linha de cron que você precisa configurar — o restante (qual comando roda, em qual frequência) é gerenciado dentro do próprio Laravel.

#### Configurar o cron do sistema operacional

```bash
# Abra o crontab do usuário que executa o projeto (ex: www-data)
sudo crontab -u www-data -e
```

Adicione a seguinte linha:

```cron
* * * * * cd /var/www/pei && php artisan schedule:run >> /dev/null 2>&1
```

> Substitua `/var/www/pei` pelo caminho real da instalação.

#### Verificar o que está agendado

```bash
php artisan schedule:list
```

O resultado deve incluir:

```
reports:process-scheduled    Hourly   Runs in background   Without overlapping
```

#### Testar o scheduler manualmente

```bash
# Executa o scheduler agora, sem esperar o próximo minuto do cron
php artisan schedule:run

# Ou executa o comando de relatórios diretamente (útil para depuração)
php artisan reports:process-scheduled
```

---

### Verificando que tudo funciona

Após configurar o Supervisor (ou systemd) e o cron, faça este checklist:

| O que verificar | Como verificar |
|---|---|
| Queue worker em execução | `sudo supervisorctl status pei-worker:*` |
| Comando registrado no scheduler | `php artisan schedule:list` |
| Cron do sistema ativo | `sudo crontab -u www-data -l` |
| Jobs com falha na fila | `php artisan queue:failed` |
| Log do scheduler de relatórios | `tail -f storage/logs/reports-scheduler.log` |
| Log geral do sistema | `tail -f storage/logs/laravel.log` |

#### Após a execução do scheduler, verificar no banco:

```sql
-- Relatórios agendados ativos e suas próximas execuções
SELECT cod_agendamento, dsc_tipo_relatorio, dsc_frequencia, dte_proxima_execucao
FROM pei.tab_relatorios_agendados
WHERE bln_ativo = true
ORDER BY dte_proxima_execucao;

-- Relatórios já gerados
SELECT dsc_tipo_relatorio, dsc_caminho_arquivo, created_at
FROM pei.tab_relatorios_gerados
ORDER BY created_at DESC
LIMIT 10;
```

---

### Reprocessar jobs com falha

Se um relatório não foi gerado por falha (erro de memória, banco temporariamente indisponível, etc.), os jobs ficam registrados na tabela `pei.failed_jobs`. Para reprocessar:

```bash
# Listar jobs com falha
php artisan queue:failed

# Reenviar um job específico pelo ID
php artisan queue:retry <id>

# Reenviar todos os jobs com falha de uma vez
php artisan queue:retry all

# Limpar jobs com falha antigos (após confirmação de que não são mais necessários)
php artisan queue:flush
```

---

## 🏛️ Arquitetura do sistema

### Multi-schema PostgreSQL

O projeto adota **Domain-Driven Design (DDD)** tanto na estrutura de código quanto no banco. Em vez de um único schema `public`, os dados são segregados em **6 schemas PostgreSQL** por domínio de negócio:

```mermaid
graph TD
    DB[Banco PostgreSQL] --> PEI[pei]
    DB --> SP[strategic_planning]
    DB --> AP[action_plan]
    DB --> PI[performance_indicators]
    DB --> RM[risk_management]
    DB --> ORG[organization]

    PEI --> U1[users · sessions · cache · jobs]
    PEI --> U2[auditoria · alertas · relatórios · configurações]
    SP --> S1[tab_pei · tab_objetivo · tab_perspectiva]
    SP --> S2[tab_identidade · tab_analise_ambiental · tab_valor]
    AP --> A1[tab_plano_de_acao · tab_entregas]
    AP --> A2[comentários · anexos · histórico · rótulos]
    PI --> P1[tab_indicador · tab_meta · tab_evolucao_indicador]
    RM --> R1[tab_risco · tab_risco_mitigacao · tab_ocorrencia]
    ORG --> O1[tab_organizacoes · tab_perfil_acesso]
```

| Schema | Propósito |
|---|---|
| `pei` | Usuários, sessões, cache, filas, tokens de acesso, auditoria, relatórios, alertas estratégicos, configurações sistêmicas e status |
| `strategic_planning` | Ciclos PEI, perspectivas BSC, objetivos, identidade (missão/visão/valores), cadeia de valor e análise ambiental (SWOT/PESTEL) |
| `action_plan` | Planos de ação, entregas (Kanban/Lista/Timeline/Calendário), rótulos, comentários, histórico de alterações e anexos |
| `performance_indicators` | Indicadores/KPIs, metas por ano, linha de base e evolução histórica |
| `risk_management` | Riscos, planos de mitigação e ocorrências registradas |
| `organization` | Organizações hierárquicas e perfis de acesso granulares |

> ⚠️ **Importante para desenvolvedores:** todos os Models declaram `$table` com o prefixo de schema explícito (ex.: `'strategic_planning.tab_pei'`). **Nunca assuma que uma tabela está em `public`** — esse schema não é utilizado pelo sistema.

### Estrutura de pastas

```text
app/
├── Livewire/                   # Componentes Livewire por domínio
│   ├── StrategicPlanning/      # PEI, Identidade, Perspectivas, Objetivos, Mapa, SWOT, PESTEL
│   ├── ActionPlan/             # Planos de Ação
│   ├── Deliverables/           # Entregas (Kanban / Lista / Timeline / Calendário)
│   ├── PerformanceIndicators/  # Indicadores e Evolução
│   ├── RiskManagement/         # Riscos e Mitigações
│   ├── Organization/           # Organizações e Perfis
│   ├── Reports/                # Relatórios
│   ├── Audit/                  # Auditoria
│   ├── Admin/                  # Configurações do sistema
│   └── Dashboard/              # Dashboard executivo
├── Models/                     # Eloquent com schema qualificado explícito
├── Services/                   # PeiGuidanceService · IndicadorCalculoService · ReportGenerationService
├── Policies/                   # OrganizacaoPolicy · PlanoDeAcaoPolicy · IndicadorPolicy · RiscoPolicy
└── Observers/                  # EntregaObserver (recálculo automático de indicadores)

resources/views/livewire/       # Views Blade organizadas por domínio
database/
├── migrations/                 # Organizadas em subpastas por domínio
└── seeders/                    # OdsSeeder · BaseStrategicSeeder · PEIDataSeeder
```

### Stack de middleware (rotas protegidas)

```
auth:sanctum → jetstream.auth_session → verified → CheckPasswordChange
```

O middleware `CheckPasswordChange` redireciona o usuário para a troca de senha obrigatória quando o campo `trocarsenha = true` está ativado no perfil.

### Rotas principais

| URL | Componente | Descrição |
|---|---|---|
| `/` | `LandingPage` | Página inicial / Mapa Estratégico |
| `/dashboard` | `Dashboard\Index` | Dashboard executivo |
| `/pei` | `MissaoVisao` | Identidade estratégica |
| `/pei/ciclos` | `ListarPeis` | Ciclos PEI |
| `/pei/mapa` | `MapaEstrategico` | Mapa estratégico visual |
| `/pei/perspectivas` | `ListarPerspectivas` | Perspectivas BSC |
| `/pei/swot` | `AnaliseSWOT` | Análise SWOT |
| `/pei/pestel` | `AnalisePESTEL` | Análise PESTEL |
| `/objetivos` | `ListarObjetivos` | Objetivos estratégicos |
| `/indicadores` | `ListarIndicadores` | Indicadores / KPIs |
| `/planos` | `ListarPlanos` | Planos de Ação |
| `/riscos` | `GestaoRiscos` | Gestão de Riscos |
| `/agenda2030` | `Agenda2030` | Painel ODS |
| `/relatorios` | `ListarRelatorios` | Relatórios |
| `/auditoria` | `ListarLogs` | Trilha de auditoria |
| `/organizacoes` | `ListarOrganizacoes` | Organizações |
| `/configuracoes` | `ConfiguracaoSistema` | Configurações do sistema |

---

## 🔐 Segurança e Controle de Acesso (RBAC + ABAC)

A autorização do sistema combina **RBAC** (Role-Based Access Control — *o que o perfil do usuário pode fazer*) com **ABAC** (Attribute-Based Access Control — *sob quais condições/atributos isso vale*), centralizada na camada de **Gates e Policies** do Laravel. A fonte única da verdade é o **banco de dados** — nunca a sessão do navegador.

> **Princípio inquebrável:** a `Session` não é fonte de permissão. Ela guarda apenas uma preferência de navegação (qual organização/PEI o usuário está vendo agora). Toda decisão de acesso deriva do perfil vinculado ao usuário no banco (`perfisAcesso()`), resolvido através de `CapacidadeResolver` e validado contra o escopo real de organizações do usuário.

### RBAC — 4 perfis fixos traduzidos em capacidades por módulo

Os perfis (`App\Models\PerfilAcesso`) são registros fixos vinculados ao usuário via a tabela `organization.rel_users_tab_organizacoes_tab_perfil_acesso` (usuário × organização × perfil × plano de ação, quando aplicável):

| Perfil | Papel |
|---|---|
| **Super Admin** | Acesso irrestrito a todos os módulos e organizações |
| **Admin de Unidade** | Gerencia dados estratégicos e planos da sua organização; cria e exclui |
| **Gestor Responsável** | Edita planos/entregas/indicadores sob sua responsabilidade direta; não exclui |
| **Gestor Substituto** | Substitui o Gestor Responsável na edição; mesmas permissões de escrita |

`App\Services\Authorization\CapacidadeResolver` traduz perfil → capacidade por módulo através de uma matriz estática (`nomPath` do módulo × perfil × habilidade), sem depender de tabelas novas:

```php
CapacidadeResolver::podeNoModulo(User $user, string $nomPath, string $ability): bool
```

Seis Gates nomeados são registrados em `AppServiceProvider::boot()` e delegam a essa matriz:

```php
Gate::define('modulo.acessar',     fn (User $u, string $nomPath) => CapacidadeResolver::podeNoModulo($u, $nomPath, 'acessar'));
Gate::define('modulo.ver-sensivel', ...);
Gate::define('modulo.criar',       ...);
Gate::define('modulo.editar',      ...);
Gate::define('modulo.excluir',     ...);
Gate::define('modulo.exportar',    ...);
```

Módulos cobertos hoje: `planejamento-estrategico`, `planos-de-acao`, `indicadores`, `riscos`, `entregas`, `organizacoes`, `usuarios`, `relatorios`, além dos restritos exclusivamente a Super Admin (`auditoria`, `admin.perfis`, `admin.configuracoes`, `graus-satisfacao`).

### ABAC — escopo de organização centralizado

`App\Concerns\ResolveEscopoOrganizacional` (trait aplicada em `User`) resolve "quais organizações o usuário pode ver/operar", eliminando a duplicação de `session('organizacao_selecionada_id')` espalhada por componentes e Policies:

| Método | Função |
|---|---|
| `organizacaoIdsPermitidas()` | Todas as organizações (Super Admin) ou apenas as vinculadas ao usuário |
| `podeAcessarOrganizacao($codOrganizacao)` | Verifica se uma organização específica está no escopo do usuário |
| `organizacaoSelecionadaId()` | Organização atualmente selecionada na sessão, **já validada** contra o escopo real — retorna `null` se a seleção estiver fora do escopo (sessão desatualizada nunca é aceita como está) |
| `aplicarEscopoOrganizacional($query, $coluna)` | Aplica `whereIn` a uma query respeitando o escopo (Super Admin não sofre filtro) |

### Hooks globais — estado do usuário e auditoria de negações

Registrados em `AppServiceProvider::registrarGatesDeAutorizacao()`:

- **`Gate::before`** — veto total para usuário inativo (`ativo = false`), antes de qualquer outra checagem.
- **`Gate::after`** — toda negação de acesso é registrada no canal de log dedicado `auditoria` (`config/logging.php`, `storage/logs/auditoria-*.log`), com usuário, habilidade e apenas classe + chave primária do model envolvido (nunca o conteúdo do registro).

### Policies por domínio

| Policy | Model | Regra |
|---|---|---|
| `OrganizationPolicy` | `Organization` | RBAC (`modulo.*`) — criar/excluir restritos a Super Admin |
| `UserPolicy` | `User` | Super Admin gerencia; usuário só vê o próprio perfil; ninguém se autoexclui |
| `PlanoDeAcaoPolicy` | `ActionPlan\PlanoDeAcao` | RBAC + ABAC (organização do plano) |
| `IndicadorPolicy` | `PerformanceIndicators\Indicador` | RBAC + ABAC (organização vinculada, sem depender de sessão bruta) |
| `RiscoPolicy` | `RiskManagement\Risco` | RBAC + ABAC (organização do risco **e** responsável pelo monitoramento) |
| `EntregaPolicy` | `ActionPlan\Entrega` | RBAC + ABAC (organização do plano de ação vinculado) |

Módulos sem Model 1:1 (Planejamento Estratégico, Relatórios, Auditoria, Admin) são protegidos diretamente nos componentes Livewire via `$this->authorize('modulo.<ability>', '<nomPath>')`, sem Policy artificial.

### Boas práticas ao estender (obrigatórias)

1. **Nunca** decida permissão a partir de `session(...)` diretamente — use `Gate::forUser($user)->allows(...)` ou `$user->can(...)`, nunca `Gate::allows(...)` sozinho (que resolve o usuário *ambiente* via `Auth::user()`, não necessariamente o `$user` recebido como parâmetro — armadilha real já corrigida neste projeto).
2. Em Policies que recebem `$user` explicitamente, sempre propague esse `$user` para os Gates internos (`Gate::forUser($user)->allows(...)`) — mantém a decisão correta em testes, jobs e comandos CLI, onde não há usuário autenticado no contexto ambiente.
3. Ao criar um novo domínio, delegue aos Gates `modulo.*` (adicionando o módulo à matriz do `CapacidadeResolver`) em vez de reimplementar checagem de perfil.
4. Ao consultar dados por organização, use `$user->podeAcessarOrganizacao(...)` / `$user->organizacaoSelecionadaId()` — nunca leia `session('organizacao_selecionada_id')` cru dentro de uma Policy ou query de escopo.
5. Testes de autorização devem autenticar o sujeito com `actingAs($user)` para exercitar o caminho real de Gate (veja `tests/Feature/Authorization/`).

### Outras medidas de segurança do sistema

| Medida | Onde | Detalhe |
|---|---|---|
| **Senha forte obrigatória** | `app/Actions/Fortify/PasswordValidationRules.php` | Mínimo 8 caracteres, maiúscula, minúscula, número e caractere especial — aplicada em cadastro, troca de senha e reset |
| **Troca de senha forçada** | `app/Http/Middleware/CheckPasswordChange.php` | Redireciona todo usuário com `trocarsenha = true` para a troca, com lista mínima de exceções (logout, o próprio formulário) |
| **Auditoria de mutações** | `owen-it/laravel-auditing` | Trilha completa (quem, o quê, quando, valor antes/depois) em todas as entidades de negócio, consultável em `/auditoria` (restrito a Super Admin) |
| **Auditoria de negações de acesso** | Canal de log `auditoria` (`Gate::after`) | Complementar à auditoria de mutações — registra tentativas negadas pelo Gate, não apenas alterações persistidas |
| **Credenciais de IA cifradas em repouso** | `pei.system_settings` | API Keys e Service Account JSON armazenados com `Crypt::encryptString` |
| **Impersonação controlada** | `App\Http\Controllers\ImpersonateController` | Restrita a Super Admin; bloqueia impersonação aninhada e autoimpersonação |
| **Hardening de sessão** | `.env` / `config/session.php` | `SESSION_DOMAIN` restrito ao host, `SESSION_SECURE_COOKIE` em produção — ver [Configuração do ambiente](#-configuração-do-ambiente-env) |

### 📖 A história por trás desta seção — o que foi investigado e corrigido em julho de 2026

Tudo o que está descrito acima não nasceu de um planejamento de arquitetura no papel — nasceu de perguntas reais feitas por quem usa o sistema no dia a dia, e de uma decisão de não deixar nenhuma delas sem resposta verificada em código. Registramos aqui o percurso, porque entender *por que* uma proteção existe é tão importante quanto saber que ela existe.

**O ponto de partida foi uma suíte de testes quebrada.** Antes de qualquer coisa, `php artisan test` rodava com **32 falhas de 58 testes**. A causa mais surpreendente não estava na lógica de negócio: `URL::forceRootUrl()`, chamado incondicionalmente no `AppServiceProvider` para o sistema funcionar corretamente atrás de um subdiretório em produção, também rodava durante os testes automatizados — e como o helper interno do Laravel para requisições de teste usa esse mesmo gerador de URL, uma chamada simples como `$this->get('/login')` acabava virando uma requisição para o domínio de produção, que o roteador de teste não reconhecia. Um efeito colateral silencioso, que derrubava dezenas de testes sem relação aparente entre si. Depois de isolar essa causa-raiz (e ajustar o `phpunit.xml` para usar a porta e o domínio corretos do ambiente de teste), a suíte já estava em 46 testes passando, 0 falhas — e esse foi o chão firme sobre o qual construímos tudo o que veio depois.

**Em seguida veio a implementação do RBAC + ABAC descrito nas seções acima** — os Gates `modulo.*`, a matriz de capacidades do `CapacidadeResolver`, o escopo de organização centralizado em `ResolveEscopoOrganizacional`, os hooks globais de `Gate::before`/`Gate::after`. No processo de escrever essa camada, dois bugs sutis, mas reais, apareceram nas Policies que já existiam: uma comparação de coluna ambígua (`cod_perfil`) que o PostgreSQL aceitava silenciosamente em certos casos e rejeitava em outros, e um uso de `Gate::allows()` sem vincular explicitamente o usuário sendo avaliado — o tipo de erro que passa despercebido em um ambiente de desenvolvimento single-user, mas que pode gerar uma decisão de autorização incorreta em produção, jobs em fila ou comandos de linha de comando.

**Depois veio a investigação do erro 500 relatado na apresentação para a diretoria.** A tela de Revisão (RAE) e mais dois módulos falhavam com erro 500 durante a demonstração. Cruzando os logs do servidor com a janela de tempo da apresentação, a causa raiz apareceu clara: três migrations criadas dias antes nunca haviam sido aplicadas ao banco de dados usado naquele ambiente específico — um problema de sincronização de ambiente, não um bug de código. Aproveitamos a investigação para também revisar e reforçar essa suíte de testes recém-estabilizada.

**O momento mais importante, porém, foi quando um cliente que havia clonado o projeto relatou uma suspeita:** ele desconfiava que a atribuição de responsabilidade em lançamentos de evolução (Indicadores, Entregas) pudesse não estar sendo respeitada corretamente. Ele estava certo — e a investigação revelou algo mais amplo do que a suspeita inicial. A causa-raiz era sistêmica: o seletor de organização no menu superior (`SeletorOrganizacao`) aceitava qualquer organização existente no banco, sem checar se o usuário autenticado realmente pertencia a ela. Como praticamente todo o sistema confia na organização selecionada na sessão para decidir permissões, essa única brecha abria caminho para vários vazamentos concretos: um Admin Unidade podia editar ou excluir indicadores de **qualquer** organização, bastando manter a sua própria selecionada no menu (`IndicadorPolicy` nunca cruzava com a organização *real* do indicador); o módulo de Revisão (RAE) não tinha **nenhuma** autorização em nenhum dos seus 11 métodos de escrita; e três outras telas (Futuro Almejado, Missão/Visão, Lições Aprendidas) também não verificavam nada.

Corrigimos a raiz e cada vazamento concreto, com testes de regressão que provam a correção — inclusive revertendo cada correção temporariamente, um de cada vez, só para confirmar que o teste realmente capturava o problema antes de reaplicá-la. Mas o processo não parou aí: ao revisar o próprio trabalho, percebemos (com uma observação valiosa de quem acompanhava de perto) que duas das correções haviam ido longe demais, bloqueando por engano a simples **visualização** de informação em vez de restringir apenas a **escrita**. Esse é um princípio que vale a pena destacar, porque molda toda a filosofia de segurança do sistema:

> **Navegar e "mergulhar" na informação — abrir o Mapa Estratégico, clicar em um objetivo, ver os detalhes de um indicador de outra organização — é livre para qualquer usuário autenticado.** A responsabilidade organizacional entra em cena apenas quando alguém tenta **escrever**: lançar uma evolução, editar um campo, salvar uma alteração, excluir um registro. Leitura é sempre aberta; escrita é sempre controlada por quem é responsável.

Ajustamos as duas telas para refletir exatamente isso e, de brinde, encontramos e corrigimos um bug antigo e não relacionado que só veio à tona durante os novos testes: o tratamento global de erro 403 do sistema tentava redirecionar até em chamadas internas do Livewire, o que quebrava a resposta com um erro técnico em vez de simplesmente negar o acesso de forma limpa.

Por fim, uma pergunta direta fechou o ciclo: **o Super Admin continua com acesso total, sem exceção?** Em vez de simplesmente responder "sim", auditamos cada Policy, o `CapacidadeResolver` e o `ResolveEscopoOrganizacional` em busca do bypass, e escrevemos quatro testes que provam, na prática, um Super Admin sem nenhum vínculo direto criando e editando registros em organizações inteiramente alheias a ele. A resposta era sim — e agora isso está garantido por teste, não apenas por leitura de código.

Ao final de todo esse percurso, a suíte de testes soma **64 testes passando, 0 falhas** — cada correção descrita acima acompanhada de pelo menos um teste que a comprova e que vai continuar protegendo o sistema contra regressões futuras. O trabalho está registrado em três branches (`feat/rbac-abac-autorizacao-testes`, `fix/grau-satisfacao-sugestao-ia-pei` e `fix/vazamento-responsabilidade-organizacao-evolucao`), com análises detalhadas em `documentacao/` para quem quiser entender cada decisão em profundidade.

---

## 💻 Desenvolvimento

### Iniciar o ambiente de desenvolvimento completo

```bash
composer dev
```

Este comando inicia em paralelo:
- **Laravel server** (`php artisan serve`) na porta 8000
- **Queue worker** (`php artisan queue:listen`) para processamento de filas
- **Vite dev server** com hot-reload de CSS e JS

### Comandos essenciais

```bash
# Servidor e build
composer dev                             # Ambiente de dev completo (server + queue + Vite)
npm run build                            # Compilar assets para produção

# Banco de dados
php artisan migrate                      # Executar novas migrations
php artisan migrate --path=database/migrations/Dominio/arquivo.php  # Migration específica
php artisan db:seed --class=NomeDoSeeder # Seeder específico (nunca rode seeders globais em produção)

# Cache e otimização
php artisan optimize:clear               # Limpar todos os caches (obrigatório após alterações de config)
php artisan route:list                   # Inspecionar rotas registradas

# Qualidade
vendor/bin/pint --dirty                  # Lint apenas dos arquivos modificados
php -l app/Livewire/MeuComponente.php    # Validar sintaxe de um arquivo PHP

# Testes
php artisan test                         # Suíte completa (Pest)
php artisan test --filter=NomeTeste      # Teste filtrado por nome
```

> ⚠️ **XAMPP com OPcache (Apache):** **nunca** rode `php artisan config:cache` ou `php artisan optimize` nesse ambiente. Esses comandos podem deixar a aplicação servindo uma configuração sem `APP_KEY`, causando erro 500 global. Se isso ocorrer, reinicie o Apache para limpar o OPcache.

### Livewire 4 — notas de compatibilidade

O projeto roda **Livewire 4.0.0**. As principais diferenças em relação à série 3.x que afetam desenvolvedores:

| Aspecto | Livewire 3.x | Livewire 4.0 |
|---|---|---|
| **Alpine.js** | Embutido, mas importação separada era comum | Embutido e gerenciado pelo Livewire; **não importe `alpinejs` separadamente** |
| **`wire:model`** | Atualização em tempo real por padrão | **Lazy por padrão** (só atualiza ao sair do campo); use `wire:model.live` para comportamento anterior |
| **URL do JS** | `/livewire/livewire.js` | `/livewire-{nonce}/livewire.js` (nonce derivado do `APP_KEY`) |
| **`asset_url` no config** | Podia ser configurado manualmente | **Deve ser `null`** — nunca hardcode o caminho do JS |
| **Evento pós-init** | `livewire:load` | `livewire:initialized` (o `livewire:load` foi removido) |
| **Hook de commit** | `Livewire.hook('message.processed', ...)` | `Livewire.hook('commit', ({ succeed }) => ...)` |

> **Alpine.js e plugins:** Para registrar plugins (`@alpinejs/mask`, `@alpinejs/focus` etc.), use o evento `livewire:init` — o `window.Alpine` já está disponível nesse momento, antes de o Alpine inicializar os componentes.

### Livewire Blaze — otimização de views em produção

O pacote [`livewire/blaze`](https://github.com/livewire/blaze) melhora a performance de renderização **inlining** os componentes Blade nas views que os utilizam, eliminando o overhead de carregamento e compilação de cada componente separado.

**Nenhuma alteração no código é necessária.** O Blaze é registrado automaticamente via package auto-discovery e atua durante o cache de views:

```bash
# Ativar as otimizações do Blaze (executar após cada deploy em produção)
php artisan view:cache

# Limpar o cache de views (necessário após alterações em componentes Blade)
php artisan view:clear
```

> Em desenvolvimento (com `composer dev`), o Blaze não interfere no ciclo de hot-reload do Vite. O `view:cache` só deve ser rodado em produção — em desenvolvimento, o Laravel re-compila as views automaticamente.

### Convenções de código

- **Idioma**: variáveis, comentários e mensagens de usuário em **Português do Brasil**
- **Componentes Livewire**: PHP em `app/Livewire/<Domínio>/`, view em `resources/views/livewire/`, nome kebab-case no Blade
- **Models**: sempre declarar `$table` com prefixo de schema (`strategic_planning.tab_pei`)
- **Chaves primárias**: UUID com `gen_random_uuid()` como default, `$incrementing = false`, `$keyType = 'string'`
- **Soft delete**: usar `deleted_at` nas tabelas de negócio
- **UI**: Bootstrap 5 + Bootstrap Icons, seguindo o padrão visual do sistema (tema claro + dark mode)
- **Commits**: PT-BR, com prefixo `feat | fix | refactor | chore | docs`
- **Migrations**: sempre novas — **jamais** altere uma migration já aplicada em banco compartilhado

### Protocolo de edição de código

1. Ler o arquivo-alvo do disco antes de qualquer edição
2. Confirmar dependências reais: rotas, componentes Livewire referenciados, includes Blade, Models
3. Após alterar qualquer arquivo PHP, validar sintaxe: `php -l caminho/do/arquivo.php`
4. Rodar o lint no que mudou: `vendor/bin/pint --dirty`

---

## 🧪 Testes e qualidade de código

```bash
# Executar toda a suíte de testes
php artisan test

# Executar apenas um teste específico
php artisan test --filter=NomeTeste

# Alternativa via Pest diretamente
vendor/bin/pest

# Verificar e corrigir estilo de código (PSR-12)
vendor/bin/pint

# Apenas arquivos modificados (mais rápido durante o desenvolvimento)
vendor/bin/pint --dirty
```

---

## 🔧 Solução de problemas frequentes

### Erro 419 — CSRF token mismatch

**Causa:** `SESSION_DOMAIN` incorreto no `.env`.
**Solução:** Confirme que `SESSION_DOMAIN` contém **apenas o host**, sem protocolo (`http://`), porta (`:8000`) ou caminho (`/fs-v1/public`). Exemplo correto: `SESSION_DOMAIN=localhost`.

### Livewire não funciona (requisições AJAX falhando)

**Causa:** `APP_URL` não corresponde ao endereço pelo qual o navegador acessa o sistema.
**Solução:** Certifique-se de que `APP_URL` está exatamente igual à URL que aparece na barra de endereços, incluindo subdiretório. Exemplo: `APP_URL=http://192.168.1.10/fs-v1/public`.
Após corrigir o `.env`, execute: `php artisan optimize:clear`.

### Ícones Bootstrap Icons não aparecem (404 nas fontes)

**Causa:** A variável `base` no `vite.config.js` não corresponde ao caminho de deploy.
**Solução:** Verifique se `base` está configurada com o caminho correto para o ambiente (ex.: `base: '/fs-v1/public/build/'` para XAMPP com subdiretório). Execute `npm run build` após qualquer alteração no `vite.config.js`.

### Erro 500 — MissingAppKey

**Causa:** No XAMPP, o OPcache do Apache pode servir um `.env` ou config em cache sem `APP_KEY`.
**Solução:** Reinicie o Apache no painel do XAMPP. **Nunca** use `php artisan config:cache` ou `optimize` em ambientes XAMPP.

### Caractere estranho (﻿) no início das respostas JSON

**Causa:** Um arquivo PHP (normalmente em `config/`) foi salvo com BOM UTF-8.
**Solução:** Abra o arquivo no editor e salve-o sem BOM. Em VSCode: canto inferior direito → `UTF-8` → "Save with Encoding" → `UTF-8` (sem BOM). Após corrigir, execute `php artisan optimize:clear`.

### Migrations falham com erro de schema

**Causa:** O banco PostgreSQL não existe ou o usuário não tem permissões suficientes.
**Solução:** Verifique que o banco foi criado conforme a seção de [Preparação do banco](#preparação-do-banco-de-dados-comum-a-todas-as-opções) e que as credenciais no `.env` estão corretas.

### "Detected multiple instances of Alpine running" no console

**Causa:** O Alpine.js está sendo importado separadamente no `app.js` além de já estar embutido no Livewire 4.
**Solução:** Remova qualquer `import Alpine from 'alpinejs'` do `app.js`. No Livewire 4, use o evento `livewire:init` para registrar plugins via `window.Alpine.plugin(...)`. Execute `npm run build` após a remoção.

### `SIDEBAR_SCROLL_KEY has already been declared` com `wire:navigate`

**Causa:** Scripts inline com `const` em partials de layout (ex.: sidebar) são re-executados no scope global a cada navegação SPA do Livewire — `const` não pode ser redeclarado.
**Solução:** Envolva o conteúdo do `<script>` em um IIFE `(function() { ... })()` e proteja os `addEventListener` com uma flag de guarda (ex.: `window._sidebarListenersInit`) para evitar duplicação.

### `pg_dump` / `psql` não encontrado no Windows

**Causa:** O PostgreSQL não está no `PATH` do sistema.
**Solução:** Adicione ao `PATH`: `C:\Program Files\PostgreSQL\<versao>\bin`. Ou use o caminho completo: `"C:\Program Files\PostgreSQL\16\bin\pg_dump.exe"`.

---

## 🔄 Migração da versão anterior (v1 → v2)

> **Esta seção é exclusiva para organizações que já utilizavam a versão anterior** do sistema ([`marcioaxn/planejamento-estrategico`](https://github.com/marcioaxn/planejamento-estrategico), construído em Laravel 8 com dados no schema `pei`). Se você está instalando pela primeira vez, pode ignorar esta seção.

**Você não precisa redigitar nenhum dado.** A migração é feita por um **único comando Artisan**, que opera **no mesmo banco PostgreSQL**, preservando todos os identificadores (UUIDs) originais — vínculos entre registros continuam válidos.

### Como funciona

O comando executa fases auditáveis e **reversíveis até a etapa final**:

| Fase | Ação | Reversível? |
|---|---|---|
| 0 — Pré-checagem | Verifica versão do PostgreSQL (≥ 9.4), solicita confirmação de backup e inspeciona o estado do banco | — |
| 1 — Quarentena | Renomeia `pei` → `legacy_pei` (preserva 100% do legado sob outro nome) | ✅ |
| 2 — Construção | Cria os 6 schemas e tabelas da v2 via `php artisan migrate` | ✅ |
| 3 — Transferência | Copia os dados de `legacy_pei` → v2, **preservando UUIDs** | ✅ (legado intacto) |
| 4 — Validação | Compara contagens de registros origem × destino | — |
| 5 — Descarte | Remove o schema legado **somente sob confirmação explícita** | ⚠️ irreversível |

### Execução

```bash
# 1. OBRIGATÓRIO — backup completo antes de qualquer coisa
pg_dump -U <usuario> -F c -b -f backup_antes_migracao.dump <banco>

# 2. Simulação — não grava nada, apenas mostra o relatório de contagens
php artisan migracao:v1-para-v2 --dry-run

# 3. Execução real
php artisan migracao:v1-para-v2

# 4. Após validar que tudo está correto, descarte o legado (opcional e irreversível)
php artisan migracao:v1-para-v2 --descartar-legado
```

> O comando é um **assistente interativo**: exibe pré-visualização dos volumes antes de gravar e faz apenas perguntas de seleção — nunca campos de texto livre. Use `--force` para execução não-interativa (automação/CI). Flags adicionais: `--migrar-auditoria`, `--status-entrega-padrao="..."`.

### O que muda e o que não migra

| Item | Comportamento |
|---|---|
| **UUIDs** | Preservados 1:1 — todas as FKs continuam válidas |
| **Senhas** | Hashes bcrypt migrados como estão — compatíveis com a v2 |
| **Entregas** | Campos sem equivalente direto na v2 são preservados em `json_propriedades` — nada se perde |
| **Auditoria** | Não é migrada por decisão de projeto — permanece disponível no backup |
| **Módulos novos da v2** | Riscos, Temas Norteadores, Agenda 2030 — nascem vazios, pois não existiam na v1 |

📖 **Documentação de apoio:**
- **Runbook do executor** (passo a passo para analistas de infraestrutura): [runbook-migracao-v1-para-v2.md](documentacao/runbook-migracao-v1-para-v2.md)
- **Mapa De→Para campo a campo**: [migracao-legado-v1-para-v2-mapa-de-para.md](documentacao/migracao-legado-v1-para-v2-mapa-de-para.md)

---

## 📚 Documentação relacionada

Todo sistema de gestão estratégica carrega, por trás do código, uma quantidade grande de conhecimento acumulado — decisões de arquitetura, o motivo real de cada correção, como o administrador deve operar o dia a dia. Guardamos esse conhecimento em documentos vivos, e não deixamos que envelheçam escondidos: sempre que uma mudança relevante acontece no sistema, os documentos abaixo são revisados na mesma rodada de trabalho. **Os quatro primeiros itens desta lista** foram atualizados logo depois da implementação do RBAC + ABAC e da correção do vazamento de responsabilidade organizacional, narrada na seção de [Segurança e Controle de Acesso](#-segurança-e-controle-de-acesso-rbac--abac) acima:

- A **documentação técnica** ganhou uma seção inteira dedicada ao novo modelo de autorização (`CapacidadeResolver`, Gates, Policies), à correção do vazamento e ao estado atual da suíte de testes (0 falhas, 64 passando).
- Os **dois manuais operacionais** (Markdown e Word, o mesmo conteúdo em dois formatos para uso diferente) passaram a explicar, em linguagem de usuário final, a nova regra de "quem pode editar o quê", por que o seletor de organização no menu superior ficou mais rigoroso, e o controle de acesso reforçado no módulo de Revisão (RAE).
- O **dicionário de dados** foi conferido campo a campo contra o banco real, para confirmar (e deixar registrado) que nenhuma tabela nova foi criada — a lógica de permissões vive inteiramente em código, não no schema do banco.

| Documento | Localização |
|---|---|
| Documentação técnica completa (v2) | [documentacao-tecnica-planejamento-estrategico-v2.md](documentacao/harness/documentacao-tecnica-planejamento-estrategico-v2.md) |
| Manual operacional (Markdown) | [manual-operacional-planejamento-estrategico-v1.md](documentacao/harness/manual-operacional-planejamento-estrategico-v1.md) |
| Manual operacional (Word/.docx) | [manual-operacional-pei-v4_20260607_15h34.docx](documentacao/harness/manual-operacional-pei-v4_20260607_15h34.docx) |
| Dicionário de dados PostgreSQL | [dicionario-dados-postgresql-planejamento-estrategico.md](documentacao/harness/dicionario-dados-postgresql-planejamento-estrategico.md) |
| Documento mestre e roadmap do sistema | [documento-mestre-evolucao-sistema-pei.md](documentacao/documento-mestre-evolucao-sistema-pei.md) |
| Agenda 2030 / ODS — integração | [agenda_2030_ods_agregado_ao_planejamento_estrategico.md](documentacao/agenda_2030_ods_agregado_ao_planejamento_estrategico.md) |
| Guia de transição completa v1 → v2 | [guia-transicao-completa-v1-para-v2.md](documentacao/guia-transicao-completa-v1-para-v2.md) |
| Guia GPPEI — MGI 2025 (PDF) | [Guia_PEI_VF.pdf](documentacao/pdf/Guia_PEI_VF.pdf) |
| Guia de Projetos — MGI (PDF) | [guia-pratico-de-projetos.pdf](documentacao/pdf/guia-pratico-de-projetos.pdf) |

---

## 📜 Licença e créditos

Software proprietário, desenvolvido e customizado para atender às necessidades específicas de gestão estratégica de organizações públicas brasileiras. O starter kit de base é open-source sob licença MIT.

- **Projeto base:** [Starter Kit Laravel Jetstream Livewire Bootstrap](https://github.com/marcioaxn/starter-kit-laravel-jetstream-livewire-bootstrap)
- **Autor:** Marcio Alessandro Xavier Neto
- **Referência metodológica:** Guia Prático de Planejamento Estratégico Institucional — GPPEI / Ministério da Gestão e da Inovação em Serviços Públicos (MGI), 2025
