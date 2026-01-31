# Roadmap: Renomeação de [Objetivo Estratégico] para [Temas Norteadores]

## Contexto
O projeto requer a alteração do termo "Objetivo Estratégico" para "Temas Norteadores". Esta mudança deve ser refletida no banco de dados, código backend (Models, Controllers, Livewire) e frontend (Views, Traduções).
É crucial manter a distinção entre "Objetivo Estratégico" (agora Temas Norteadores) e "Objetivo" (entidade distinta que permanece inalterada).

## Plano de Execução

### 1. Preparação
- [x] Análise de impacto e identificação de arquivos.
- [x] Criação deste roadmap.
- [x] Commit do estado atual (Backup).

### 2. Banco de Dados (Migration)
- [x] Criar migration para:
    - Renomear tabela `tab_objetivo_estrategico` para `tab_tema_norteador` (Schema `strategic_planning`).
    - Renomear coluna `cod_objetivo_estrategico` para `cod_tema_norteador`.
    - Renomear coluna `nom_objetivo_estrategico` para `nom_tema_norteador`.

### 3. Backend (Models & Logic)
- [x] Renomear Model `App\Models\StrategicPlanning\ObjetivoEstrategico.php` para `TemaNorteador.php`.
- [x] Refatorar classe `TemaNorteador`:
    - Atualizar `table`, `primaryKey`, `fillable`.
    - Atualizar relacionamentos inversos (se houver).
- [x] Atualizar relacionamentos em outros Models:
    - `App\Models\StrategicPlanning\PEI.php` (provável `hasMany`).
    - Outros models que referenciam `ObjetivoEstrategico`.
- [x] Renomear Seeder `ObjetivoEstrategicoSeeder` para `TemaNorteadorSeeder`.
- [x] Atualizar Service `ReportGenerationService`.

### 4. Livewire Components
- [x] Renomear `App\Livewire\StrategicPlanning\GerenciarObjetivosEstrategicos.php` para `GerenciarTemasNorteadores.php`.
- [x] Refatorar classe `GerenciarTemasNorteadores`:
    - Atualizar referências ao Model `TemaNorteador`.
    - Atualizar nomes de variáveis (ex: `$objetivosEstrategicos` -> `$temasNorteadores`).
    - Atualizar view renderizada.
- [x] Atualizar componentes relacionados (`DetalharPei`, `MapaEstrategico`, `ListarRelatorios`).

### 5. Frontend (Views & Blades)
- [x] Renomear `resources/views/livewire/p-e-i/gerenciar-objetivos-estrategicos.blade.php` para `gerenciar-temas-norteadores.blade.php`.
- [x] Atualizar conteúdo da View:
    - Textos "Objetivo Estratégico" -> "Tema Norteador".
    - Variáveis e loops.
- [x] Buscar e substituir em outras views:
    - `relatorios/executivo.blade.php`
    - `relatorios/identidade.blade.php`
    - `relatorios/integrado.blade.php`
    - `relatorio/listar-relatorios.blade.php`
    - `layouts/app.blade.php` (Sidebar)

### 6. Verificação e Limpeza
- [x] Rodar testes (Verificação manual de compilação).
- [x] Atualizar rotas (`web.php`).
- [x] Commit final.