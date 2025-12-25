# REQUISITOS NÃO-FUNCIONAIS
## Sistema de Planejamento Estratégico

**Versão:** 1.0
**Data:** 23/12/2025

---

## ÍNDICE

1. [Performance](#1-performance)
2. [Segurança](#2-segurança)
3. [Usabilidade](#3-usabilidade)
4. [Escalabilidade](#4-escalabilidade)
5. [Confiabilidade](#5-confiabilidade)
6. [Manutenibilidade](#6-manutenibilidade)
7. [Compatibilidade](#7-compatibilidade)
8. [Padrões e Conformidade](#8-padrões-e-conformidade)

---

## 1. PERFORMANCE

### RNF-001: Tempo de Resposta de Páginas

**Descrição:** O sistema deve carregar páginas rapidamente para garantir boa experiência do usuário.

**Critérios:**
- **Páginas simples** (login, listagens): ≤ 1 segundo
- **Páginas com gráficos** (dashboards): ≤ 2 segundos
- **Relatórios complexos**: ≤ 3 segundos
- **Exportação PDF/Excel**: ≤ 5 segundos (documentos até 100 páginas)

**Medição:**
- Usar Laravel Debugbar em desenvolvimento
- Monitorar com New Relic ou similar em produção
- Testes de carga com Apache JMeter

**Prioridade:** ALTA

---

### RNF-002: Otimização de Consultas ao Banco

**Descrição:** Consultas SQL devem ser otimizadas para minimizar tempo de execução.

**Critérios:**
- Uso de **Eager Loading** em relacionamentos Eloquent (evitar problema N+1)
- **Índices** em colunas frequentemente consultadas (organizacao, pei, usuario)
- Consultas complexas ≤ 500ms
- Uso de **Query Builder** ou **Raw SQL** quando necessário para performance
- Cache de consultas frequentes (Redis ou Memcached)

**Exemplo de otimização:**
```php
// ❌ Problema N+1
$objetivos = ObjetivoEstrategico::all();
foreach ($objetivos as $obj) {
    echo $obj->perspectiva->dsc_perspectiva; // Query adicional a cada iteração
}

// ✅ Eager Loading
$objetivos = ObjetivoEstrategico::with('perspectiva')->get();
foreach ($objetivos as $obj) {
    echo $obj->perspectiva->dsc_perspectiva; // Sem query adicional
}
```

**Prioridade:** ALTA

---

### RNF-003: Cache de Dados

**Descrição:** Implementar estratégia de cache para dados frequentemente acessados.

**Critérios:**
- **Cache de configurações** (perfis, status, tipos de execução): TTL 24 horas
- **Cache de hierarquia de organizações**: TTL 12 horas
- **Cache de dashboards**: TTL 15 minutos
- **Cache de relatórios**: TTL 30 minutos
- Invalidação de cache ao editar/excluir registros relacionados
- Uso de **tags de cache** para invalidação granular

**Tecnologia:** Redis (recomendado) ou File Cache

**Prioridade:** MÉDIA

---

### RNF-004: Paginação e Lazy Loading

**Descrição:** Listagens grandes devem ser paginadas.

**Critérios:**
- Paginação de **20 itens por página** (padrão)
- Opção de alterar para 50 ou 100 itens
- Uso de `simplePaginate()` quando total de páginas não é necessário
- Lazy Loading de gráficos e imagens (carregar sob demanda)
- Infinite Scroll em feeds (se aplicável)

**Prioridade:** ALTA

---

### RNF-005: Otimização de Assets (CSS/JS)

**Descrição:** Arquivos estáticos devem ser otimizados.

**Critérios:**
- Minificação de CSS e JavaScript (via Laravel Mix/Vite)
- Concatenação de arquivos
- Compressão Gzip/Brotli no servidor
- Uso de CDN para bibliotecas externas (Bootstrap, Chart.js)
- Versionamento de assets (cache busting)
- Imagens otimizadas (WebP quando possível)

**Ferramentas:**
- Laravel Vite (build)
- TinyPNG (compressão de imagens)

**Prioridade:** MÉDIA

---

## 2. SEGURANÇA

### RNF-010: Autenticação e Autorização

**Descrição:** Controle rigoroso de acesso.

**Critérios:**
- **Laravel Jetstream** com Livewire para autenticação
- Senha criptografada (bcrypt)
- Validação de força de senha (mínimo 8 caracteres, 1 maiúscula, 1 número, 1 especial)
- Bloqueio de conta após 5 tentativas falhas (15 minutos)
- Sessões com timeout de 12 horas de inatividade
- Logout automático ao fechar navegador (se "Lembrar-me" não marcado)
- **Autenticação de dois fatores (2FA)** opcional (via Jetstream)
- Middleware de autorização em todas as rotas protegidas
- Policies para controle granular de permissões

**Prioridade:** CRÍTICA

---

### RNF-011: Proteção contra Vulnerabilidades OWASP Top 10

**Descrição:** Sistema deve estar protegido contra ataques comuns.

**Critérios:**
- **SQL Injection**: Uso exclusivo de Eloquent ORM e Query Builder com bindings
- **XSS (Cross-Site Scripting)**: Escape de outputs (`{{ }}` em Blade)
- **CSRF (Cross-Site Request Forgery)**: Token CSRF em todos os formulários
- **Injeção de Comandos**: Validação estrita de inputs
- **Exposição de Dados Sensíveis**: Nunca retornar senhas ou tokens em APIs
- **Controle de Acesso Quebrado**: Verificar permissões em cada action
- **Configurações Incorretas**: Desabilitar `APP_DEBUG` em produção
- **Desserialização Insegura**: Evitar `unserialize()` de dados não confiáveis
- **Uso de Componentes Vulneráveis**: Manter dependências atualizadas (`composer update`)

**Ferramentas de Verificação:**
- OWASP ZAP
- SonarQube
- Laravel Security Checker

**Prioridade:** CRÍTICA

---

### RNF-012: Criptografia de Dados Sensíveis

**Descrição:** Dados críticos devem ser criptografados.

**Critérios:**
- **Senhas**: Bcrypt (padrão Laravel)
- **Tokens de API**: Hash SHA256
- **Dados em trânsito**: HTTPS obrigatório (SSL/TLS)
- **Dados em repouso**: Criptografia de backup de banco de dados
- **Variáveis de ambiente**: `.env` protegido (fora do webroot)

**Prioridade:** ALTA

---

### RNF-013: Auditoria e Logs de Segurança

**Descrição:** Registrar todas as ações críticas.

**Critérios:**
- Log de autenticação (login/logout bem-sucedido e falhas)
- Log de alterações em dados críticos (via pacote `owen-it/laravel-auditing`)
- Armazenar: Usuário, IP, Data/Hora, Ação, Valores antes/depois
- Logs de erro (Laravel Log)
- Retenção de logs: mínimo 12 meses
- Proteção de logs contra alteração (append-only)

**Prioridade:** ALTA

---

### RNF-014: Backup e Recuperação

**Descrição:** Dados devem ser protegidos contra perda.

**Critérios:**
- Backup diário do banco de dados (PostgreSQL dump)
- Backup semanal completo (DB + arquivos)
- Retenção de backups: 30 dias diários + 12 meses mensais
- Armazenamento em local externo (AWS S3, Google Cloud Storage)
- Teste de restauração mensal
- RTO (Recovery Time Objective): ≤ 4 horas
- RPO (Recovery Point Objective): ≤ 24 horas

**Ferramentas:**
- Laravel Backup (spatie/laravel-backup)
- Cron jobs

**Prioridade:** CRÍTICA

---

## 3. USABILIDADE

### RNF-020: Interface Intuitiva

**Descrição:** Sistema deve ser fácil de usar.

**Critérios:**
- Layout responsivo (Bootstrap 5)
- Navegação clara (breadcrumbs, menu lateral, menu superior)
- Botões com labels descritivos e ícones
- Cores consistentes (paleta definida)
- Feedback visual de ações (toasts, alerts, spinners)
- Confirmação antes de ações destrutivas (excluir, resetar)
- Mensagens de erro claras e acionáveis
- Tooltips em campos complexos

**Prioridade:** ALTA

---

### RNF-021: Acessibilidade (WCAG 2.1 Nível AA)

**Descrição:** Sistema deve ser acessível a usuários com deficiência.

**Critérios:**
- Contraste de cores adequado (mínimo 4.5:1)
- Navegação por teclado (tab, enter, esc)
- Labels em todos os campos de formulário
- Atributos ARIA (aria-label, aria-describedby)
- Alt text em imagens
- Foco visual claro
- Tamanho de fonte ajustável
- Compatibilidade com leitores de tela (NVDA, JAWS)

**Ferramentas de Teste:**
- WAVE (Web Accessibility Evaluation Tool)
- axe DevTools

**Prioridade:** MÉDIA

---

### RNF-022: Responsividade

**Descrição:** Sistema deve funcionar em diferentes tamanhos de tela.

**Critérios:**
- **Desktop**: 1920x1080 (otimizado)
- **Laptop**: 1366x768 (suportado)
- **Tablet**: 768x1024 (suportado)
- **Mobile**: 375x667 (visualização básica)
- Gráficos adaptáveis (Chart.js responsive)
- Tabelas com scroll horizontal em telas pequenas
- Menu hambúrguer em mobile

**Teste:** BrowserStack ou responsivedesignchecker.com

**Prioridade:** ALTA

---

### RNF-023: Internacionalização (i18n)

**Descrição:** Sistema deve suportar múltiplos idiomas (futuro).

**Critérios:**
- Uso de arquivos de tradução Laravel (`resources/lang/`)
- Idiomas iniciais: Português (pt-BR) - padrão
- Preparar estrutura para: Inglês (en), Espanhol (es)
- Formato de data/hora por locale
- Formato de número por locale (separador decimal)

**Prioridade:** BAIXA (Fase 2)

---

## 4. ESCALABILIDADE

### RNF-030: Suporte a Múltiplas Organizações

**Descrição:** Sistema deve suportar crescimento do número de organizações.

**Critérios:**
- Até **100 organizações** sem degradação de performance
- Até **1.000 usuários** simultâneos
- Hierarquia de até **10 níveis** de profundidade
- Particionamento de dados por organização (soft)

**Prioridade:** ALTA

---

### RNF-031: Suporte a Grande Volume de Dados

**Descrição:** Sistema deve lidar com crescimento de dados.

**Critérios:**
- Até **10.000 objetivos estratégicos**
- Até **50.000 planos de ação**
- Até **100.000 indicadores**
- Até **5 milhões de registros de evolução** (histórico de 10+ anos)
- Queries otimizadas com índices
- Arquivamento de dados antigos (> 5 anos) em tabelas de histórico

**Prioridade:** MÉDIA

---

### RNF-032: Arquitetura Preparada para Load Balancing

**Descrição:** Permitir distribuição de carga futura.

**Critérios:**
- Sessões armazenadas em banco/Redis (não em filesystem)
- Arquivos de upload em storage compartilhado (S3) ou NFS
- Cache centralizado (Redis)
- Stateless (sem armazenamento local de estado)

**Prioridade:** BAIXA (Fase 2)

---

## 5. CONFIABILIDADE

### RNF-040: Disponibilidade

**Descrição:** Sistema deve estar disponível o máximo de tempo possível.

**Critérios:**
- **Uptime**: ≥ 99% (SLA)
- Janela de manutenção: Domingos 02:00 - 04:00 (2 horas)
- Notificação prévia de manutenção (48 horas)
- Página de status (status.dominio.com.br)

**Prioridade:** ALTA

---

### RNF-041: Tratamento de Erros

**Descrição:** Erros devem ser tratados graciosamente.

**Critérios:**
- **Erros de validação**: Mensagens claras ao usuário, destacar campos
- **Erros de banco**: Log detalhado, mensagem genérica ao usuário
- **Erros 404**: Página personalizada com link para home
- **Erros 403**: Mensagem "Sem permissão" com link para voltar
- **Erros 500**: Página personalizada, log detalhado, notificação ao admin
- Uso de `try-catch` em operações críticas
- Fallback para funcionalidades não críticas (ex: gráfico não carrega, mostrar tabela)

**Prioridade:** ALTA

---

### RNF-042: Testes Automatizados

**Descrição:** Código deve ser testado.

**Critérios:**
- **Testes de Feature**: Cobertura ≥ 60% das funcionalidades críticas
- **Testes de Unit**: Cobertura ≥ 40% dos métodos de models e services
- **Testes de Browser** (Laravel Dusk): Fluxos principais (login, cadastro, consulta)
- CI/CD com GitHub Actions ou GitLab CI
- Testes executados automaticamente em cada push

**Ferramentas:**
- PHPUnit (padrão Laravel)
- Laravel Dusk (testes E2E)

**Prioridade:** MÉDIA

---

## 6. MANUTENIBILIDADE

### RNF-050: Código Limpo e Documentado

**Descrição:** Código deve ser legível e manutenível.

**Critérios:**
- **PSR-12** (PHP Standards Recommendations)
- **Convenções Laravel** (naming, estrutura de pastas)
- DocBlocks (PHPDoc) em classes e métodos públicos
- Comentários em lógica complexa
- Nomes descritivos de variáveis e métodos
- DRY (Don't Repeat Yourself) - evitar duplicação
- SOLID principles
- Separação de responsabilidades (Controllers slim, lógica em Services)

**Ferramentas:**
- PHP CS Fixer (formatação automática)
- PHPStan (análise estática)

**Prioridade:** ALTA

---

### RNF-051: Versionamento de Código

**Descrição:** Código deve ser versionado adequadamente.

**Critérios:**
- Git como controle de versão
- Estratégia de branching: **Git Flow**
  - `main`: produção estável
  - `develop`: desenvolvimento
  - `feature/*`: novas funcionalidades
  - `hotfix/*`: correções urgentes
- Commits descritivos (Conventional Commits)
- Pull Requests com code review antes de merge
- Tags de release (v1.0.0, v1.1.0, etc.)

**Prioridade:** ALTA

---

### RNF-052: Gestão de Dependências

**Descrição:** Dependências devem ser gerenciadas corretamente.

**Critérios:**
- **Composer** para dependências PHP
- **NPM** para dependências JavaScript
- Manter dependências atualizadas (review mensal)
- Evitar dependências abandonadas
- Lock files commitados (`composer.lock`, `package-lock.json`)
- Verificação de vulnerabilidades (GitHub Dependabot)

**Prioridade:** ALTA

---

## 7. COMPATIBILIDADE

### RNF-060: Navegadores Suportados

**Descrição:** Sistema deve funcionar nos principais navegadores.

**Critérios:**
- **Chrome** ≥ 100 (otimizado)
- **Firefox** ≥ 100 (suportado)
- **Edge** ≥ 100 (suportado)
- **Safari** ≥ 15 (suportado)
- **Opera** ≥ 85 (suportado)
- **Internet Explorer**: ❌ Não suportado

**Prioridade:** ALTA

---

### RNF-061: Ambiente de Servidor

**Descrição:** Requisitos de infraestrutura.

**Critérios:**
- **Sistema Operacional**: Linux (Ubuntu 22.04 ou Debian 11+)
- **Servidor Web**: Nginx ≥ 1.20 ou Apache ≥ 2.4
- **PHP**: ≥ 8.2 (requisito Laravel 12)
- **PostgreSQL**: ≥ 14
- **Redis**: ≥ 6 (cache e sessões)
- **Composer**: ≥ 2.5
- **Node.js**: ≥ 18 (build de assets)

**Extensões PHP:**
- `pdo_pgsql`, `mbstring`, `xml`, `bcmath`, `gd`, `redis`, `zip`

**Prioridade:** CRÍTICA

---

### RNF-062: Compatibilidade com Banco Legado

**Descrição:** Sistema deve funcionar 100% com banco existente.

**Critérios:**
- **Não alterar** estrutura de tabelas existentes
- **Não renomear** colunas ou tabelas
- Respeitar UUIDs como chave primária
- Respeitar soft deletes (`deleted_at`)
- Respeitar relacionamentos existentes
- Apenas **adicionar** novas tabelas se necessário (comentários, notificações)

**Prioridade:** CRÍTICA

---

## 8. PADRÕES E CONFORMIDADE

### RNF-070: Padrões de Desenvolvimento

**Descrição:** Seguir boas práticas da comunidade.

**Critérios:**
- **Laravel Best Practices**
- **RESTful** para rotas (se houver API)
- **SPA-like** com Livewire (sem full page reloads)
- **Repository Pattern** (opcional, se complexidade justificar)
- **Service Layer** para lógica de negócio complexa

**Prioridade:** ALTA

---

### RNF-071: Conformidade com LGPD

**Descrição:** Sistema deve respeitar Lei Geral de Proteção de Dados.

**Critérios:**
- **Consentimento**: Não aplicável (sistema interno)
- **Minimização de dados**: Coletar apenas dados necessários
- **Direito ao esquecimento**: Permitir exclusão de usuário (hard delete mediante solicitação)
- **Portabilidade**: Permitir exportação de dados do usuário (JSON/Excel)
- **Segurança**: Implementar medidas técnicas adequadas (criptografia, logs, backup)
- **Responsabilidade**: Designar DPO (Data Protection Officer) no cliente

**Prioridade:** ALTA

---

### RNF-072: Documentação do Sistema

**Descrição:** Sistema deve ser bem documentado.

**Critérios:**
- **README.md**: Instruções de instalação e configuração
- **CHANGELOG.md**: Histórico de versões e alterações
- **Documentação de API** (se houver): Swagger/OpenAPI
- **Manual do Usuário**: PDF com capturas de tela e passo-a-passo
- **Manual Técnico**: Arquitetura, fluxos, diagramas
- **Wiki interna**: Decisões arquiteturais, troubleshooting

**Prioridade:** MÉDIA

---

## RESUMO DE PRIORIDADES

| ID | Requisito | Prioridade |
|----|-----------|-----------|
| RNF-001 | Tempo de Resposta | ALTA |
| RNF-002 | Otimização de Consultas | ALTA |
| RNF-003 | Cache de Dados | MÉDIA |
| RNF-004 | Paginação | ALTA |
| RNF-005 | Otimização de Assets | MÉDIA |
| RNF-010 | Autenticação e Autorização | CRÍTICA |
| RNF-011 | Proteção OWASP | CRÍTICA |
| RNF-012 | Criptografia | ALTA |
| RNF-013 | Auditoria e Logs | ALTA |
| RNF-014 | Backup | CRÍTICA |
| RNF-020 | Interface Intuitiva | ALTA |
| RNF-021 | Acessibilidade | MÉDIA |
| RNF-022 | Responsividade | ALTA |
| RNF-023 | Internacionalização | BAIXA |
| RNF-030 | Múltiplas Organizações | ALTA |
| RNF-031 | Grande Volume de Dados | MÉDIA |
| RNF-032 | Load Balancing | BAIXA |
| RNF-040 | Disponibilidade | ALTA |
| RNF-041 | Tratamento de Erros | ALTA |
| RNF-042 | Testes Automatizados | MÉDIA |
| RNF-050 | Código Limpo | ALTA |
| RNF-051 | Versionamento | ALTA |
| RNF-052 | Gestão de Dependências | ALTA |
| RNF-060 | Navegadores | ALTA |
| RNF-061 | Ambiente de Servidor | CRÍTICA |
| RNF-062 | Compatibilidade com Banco | CRÍTICA |
| RNF-070 | Padrões de Desenvolvimento | ALTA |
| RNF-071 | Conformidade LGPD | ALTA |
| RNF-072 | Documentação | MÉDIA |

---

## NOTAS DE IMPLEMENTAÇÃO

### Checklist de Qualidade

Antes de cada deploy em produção, verificar:

- [ ] Todos os testes passando (PHPUnit, Dusk)
- [ ] `APP_DEBUG=false` em produção
- [ ] HTTPS configurado
- [ ] Backup configurado e testado
- [ ] Logs rotacionando corretamente
- [ ] Cache configurado (Redis)
- [ ] Sessões em Redis/Database
- [ ] Email configurado (SMTP/SES)
- [ ] Cron jobs configurados
- [ ] Monitoramento ativo (Uptime, Logs)
- [ ] Auditoria funcionando
- [ ] Permissões de arquivo corretas (storage writable)
- [ ] `.env` protegido
- [ ] Composer otimizado (`composer install --optimize-autoloader --no-dev`)
- [ ] Assets buildados (`npm run build`)

---

**Próximo Documento:** 04-MODELOS-ELOQUENT.md
