# Roadmap: Renomeação de [Objetivo Estratégico] para [Temas Norteadores]

## Contexto
O projeto requer a alteração do termo "Objetivo Estratégico" para "Temas Norteadores". Esta mudança deve ser refletida no banco de dados, código backend (Models, Controllers, Livewire) e frontend (Views, Traduções).
É crucial manter a distinção entre "Objetivo Estratégico" (agora Temas Norteadores) e "Objetivo" (entidade distinta que permanece inalterada).

## Plano de Execução

### 1. Preparação
- [x] Análise de impacto e identificação de arquivos.
- [x] Criação deste roadmap.
- [ ] Commit do estado atual (Backup).

### 2. Banco de Dados (Migration)
- [ ] Criar migration para:
    - Renomear tabela `tab_objetivo_estrategico` para `tab_tema_norteador`.
    - Renomear coluna `cod_objetivo_estrategico` para `cod_tema_norteador`.
    - Renomear coluna `nom_objetivo_estrategico` para `nom_tema_norteador`.

### 3. Backend (Models & Logic)
- [ ] Renomear Model `App\Models\StrategicPlanning\ObjetivoEstrategico.php` para `TemaNorteador.php`.
- [ ] Refatorar classe `TemaNorteador`:
    - Atualizar `table`, `primaryKey`, `fillable`.
    - Atualizar relacionamentos inversos (se houver).
- [ ] Atualizar relacionamentos em outros Models:
    - `App\Models\StrategicPlanning\PEI.php` (provável `hasMany`).
    - Outros models que referenciam `ObjetivoEstrategico`.

### 4. Livewire Components
- [ ] Renomear `App\Livewire\StrategicPlanning\GerenciarObjetivosEstrategicos.php` para `GerenciarTemasNorteadores.php`.
- [ ] Refatorar classe `GerenciarTemasNorteadores`:
    - Atualizar referências ao Model `TemaNorteador`.
    - Atualizar nomes de variáveis (ex: `$objetivosEstrategicos` -> `$temasNorteadores`).
    - Atualizar view renderizada.

### 5. Frontend (Views & Blades)
- [ ] Renomear `resources/views/livewire/p-e-i/gerenciar-objetivos-estrategicos.blade.php` para `gerenciar-temas-norteadores.blade.php`.
- [ ] Atualizar conteúdo da View:
    - Textos "Objetivo Estratégico" -> "Tema Norteador".
    - Variáveis e loops.
- [ ] Buscar e substituir em outras views (ex: relatórios, dashboards) onde o termo aparece.

### 6. Verificação e Limpeza
- [ ] Rodar testes (se houver).
- [ ] Verificar integridade da aplicação (navegação, CRUD).
- [ ] Commit final.
