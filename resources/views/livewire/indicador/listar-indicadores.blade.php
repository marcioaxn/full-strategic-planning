<div>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Indicadores (KPIs)</li>
                    </ol>
                </nav>
                <h2 class="h4 fw-bold mb-0">Indicadores de Desempenho</h2>
            </div>
            <div class="d-flex gap-2">
                @if($organizacaoId)
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary rounded-pill px-3 shadow-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-download me-1"></i> Exportar
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                            <li><a class="dropdown-item" href="{{ route('relatorios.indicadores.pdf') }}"><i class="bi bi-file-earmark-pdf text-danger me-2"></i> PDF</a></li>
                            <li><a class="dropdown-item" href="{{ route('relatorios.indicadores.excel') }}"><i class="bi bi-file-earmark-excel text-success me-2"></i> Excel</a></li>
                        </ul>
                    </div>
                @endif
                @if($organizacaoId)
                    <button wire:click="create" class="btn btn-primary gradient-theme-btn">
                        <i class="bi bi-plus-lg me-2"></i>Novo Indicador
                    </button>
                @endif
            </div>
        </div>
    </x-slot>

    @if (session()->has('status'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(!$organizacaoId)
        <div class="alert alert-warning shadow-sm border-0 d-flex align-items-center p-4" role="alert">
            <i class="bi bi-building-exclamation fs-2 me-4"></i>
            <div>
                <h5 class="alert-heading fw-bold mb-1">Selecione uma Organização</h5>
                <p class="mb-0">Selecione uma organização no menu superior para listar e gerenciar indicadores.</p>
            </div>
        </div>
    @else
        <!-- Filtros -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3 bg-light rounded-3">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" wire:model.live.debounce="search" class="form-control border-start-0 ps-0" placeholder="Buscar por nome do indicador...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select wire:model.live="filtroVinculo" class="form-select">
                            <option value="">Todos os Vínculos</option>
                            <option value="Objetivo">Vínculo com Objetivo</option>
                            <option value="Plano">Vínculo com Plano</option>
                        </select>
                    </div>
                    <div class="col-md-3 text-end">
                        <div wire:loading class="spinner-border text-primary spinner-border-sm" role="status"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela -->
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">Indicador / Descrição</th>
                            <th>Vínculo</th>
                            <th>Período / Unidade</th>
                            <th>Performance</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($indicadores as $ind)
                            <tr>
                                <td class="ps-4 py-3">
                                    <span class="fw-bold text-dark d-block mb-1">{{ $ind->nom_indicador }}</span>
                                    <small class="text-muted text-truncate d-block" style="max-width: 350px;">{{ $ind->dsc_indicador }}</small>
                                </td>
                                <td>
                                    @if($ind->cod_objetivo_estrategico)
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3">
                                            <i class="bi bi-bullseye me-1"></i> Objetivo
                                        </span>
                                        <small class="d-block text-muted mt-1 small-vinculo">{{ Str::limit($ind->objetivoEstrategico->nom_objetivo_estrategico, 40) }}</small>
                                    @else
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-3">
                                            <i class="bi bi-list-task me-1"></i> Plano
                                        </span>
                                        <small class="d-block text-muted mt-1 small-vinculo">{{ Str::limit($ind->planoDeAcao->dsc_plano_de_acao, 40) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <small class="text-dark fw-semibold">{{ $ind->dsc_unidade_medida }}</small>
                                        <small class="text-muted">{{ $ind->dsc_periodo_medicao }}</small>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $atingimento = $ind->calcularAtingimento();
                                        $corFarol = $ind->getCorFarol();
                                    @endphp
                                    <div class="d-flex align-items-center">
                                        <div class="farol-dot me-2" style="background-color: {{ $corFarol ?: '#dee2e6' }}; shadow: 0 0 5px {{ $corFarol }}88;"></div>
                                        <span class="fw-bold fs-6">{{ number_format($atingimento, 1) }}%</span>
                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                            <li><h6 class="dropdown-header small text-uppercase">Lançamentos</h6></li>
                                            <li><a class="dropdown-item" href="{{ route('indicadores.detalhes', $ind->cod_indicador) }}"><i class="bi bi-eye me-2 text-primary"></i> Ficha Técnica</a></li>
                                            <li><a class="dropdown-item" href="{{ route('indicadores.evolucao', $ind->cod_indicador) }}"><i class="bi bi-graph-up-arrow me-2 text-success"></i> Lançar Evolução</a></li>
                                            <li><button class="dropdown-item" wire:click="abrirMetas('{{ $ind->cod_indicador }}')"><i class="bi bi-bullseye me-2 text-primary"></i> Gerenciar Metas</button></li>
                                            <li><button class="dropdown-item" wire:click="abrirLinhaBase('{{ $ind->cod_indicador }}')"><i class="bi bi-bar-chart-steps me-2 text-warning"></i> Linha de Base</button></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><h6 class="dropdown-header small text-uppercase">Configuração</h6></li>
                                            <li><button class="dropdown-item" wire:click="edit('{{ $ind->cod_indicador }}')"><i class="bi bi-pencil me-2"></i> Editar</button></li>
                                            <li><button class="dropdown-item text-danger" wire:click="confirmDelete('{{ $ind->cod_indicador }}')"><i class="bi bi-trash me-2"></i> Excluir</button></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-bar-chart fs-1 opacity-25 mb-3 d-block"></i>
                                    Nenhum indicador encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-top py-3">
                {{ $indicadores->links() }}
            </div>
        </div>
    @endif

    <!-- Modal Criar/Editar -->
    <div class="modal fade @if($showModal) show @endif" tabindex="-1" style="@if($showModal) display: block; background: rgba(0,0,0,0.5); @else display: none; @endif">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header gradient-theme text-white border-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-sliders me-2"></i> {{ $indicadorId ? 'Configurar Indicador' : 'Novo Indicador' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showModal', false)"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body p-4 bg-light bg-opacity-50">
                        <div class="row g-4">
                            <!-- Bloco 1: Identificação -->
                            <div class="col-lg-7">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body p-4">
                                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Identificação Principal</h6>
                                        <div class="mb-3">
                                            <label class="form-label text-muted small text-uppercase fw-bold">Nome do Indicador</label>
                                            <input type="text" wire:model="form.nom_indicador" class="form-control @error('form.nom_indicador') is-invalid @enderror" placeholder="Ex: Índice de Satisfação do Cidadão">
                                            @error('form.nom_indicador') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-muted small text-uppercase fw-bold">Descrição / Conceito</label>
                                            <textarea wire:model="form.dsc_indicador" class="form-control @error('form.dsc_indicador') is-invalid @enderror" rows="3" placeholder="O que este indicador mede exatamente?"></textarea>
                                            @error('form.dsc_indicador') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label text-muted small text-uppercase fw-bold">Observações Técnicas</label>
                                            <textarea wire:model="form.txt_observacao" class="form-control" rows="2"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bloco 2: Vínculo Estratégico -->
                            <div class="col-lg-5">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body p-4">
                                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Vínculo Estratégico</h6>
                                        <div class="mb-4">
                                            <label class="form-label text-muted small text-uppercase fw-bold d-block">Este indicador pertence a um:</label>
                                            <div class="btn-group w-100" role="group">
                                                <input type="radio" class="btn-check" wire:model.live="form.dsc_tipo" value="Objetivo" id="v_obj" autocomplete="off">
                                                <label class="btn btn-outline-primary" for="v_obj"><i class="bi bi-bullseye me-1"></i> Objetivo</label>

                                                <input type="radio" class="btn-check" wire:model.live="form.dsc_tipo" value="Plano" id="v_plan" autocomplete="off">
                                                <label class="btn btn-outline-info" for="v_plan"><i class="bi bi-list-task me-1"></i> Plano de Ação</label>
                                            </div>
                                        </div>

                                        @if($form['dsc_tipo'] === 'Objetivo')
                                            <div class="mb-3 animate-fade-in">
                                                <label class="form-label text-muted small text-uppercase fw-bold">Selecionar Objetivo</label>
                                                <select wire:model="form.cod_objetivo_estrategico" class="form-select @error('form.cod_objetivo_estrategico') is-invalid @enderror">
                                                    <option value="">Escolha o objetivo...</option>
                                                    @foreach($objetivos as $obj)
                                                        <option value="{{ $obj->cod_objetivo_estrategico }}">{{ $obj->nom_objetivo_estrategico }}</option>
                                                    @endforeach
                                                </select>
                                                @error('form.cod_objetivo_estrategico') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                        @else
                                            <div class="mb-3 animate-fade-in">
                                                <label class="form-label text-muted small text-uppercase fw-bold">Selecionar Plano de Ação</label>
                                                <select wire:model="form.cod_plano_de_acao" class="form-select @error('form.cod_plano_de_acao') is-invalid @enderror">
                                                    <option value="">Escolha o plano...</option>
                                                    @foreach($planos as $plano)
                                                        <option value="{{ $plano->cod_plano_de_acao }}">{{ $plano->dsc_plano_de_acao }}</option>
                                                    @endforeach
                                                </select>
                                                @error('form.cod_plano_de_acao') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                        @endif
                                        
                                        <div class="mb-0">
                                            <label class="form-label text-muted small text-uppercase fw-bold">Peso do Indicador</label>
                                            <input type="number" wire:model="form.num_peso" class="form-control" min="1" max="100">
                                            <small class="text-muted">Importância relativa para o cálculo do índice global.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bloco 3: Metodologia e Medição -->
                            <div class="col-12">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body p-4">
                                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Metodologia e Unidade de Medida</h6>
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label class="form-label text-muted small text-uppercase fw-bold">Unidade de Medida</label>
                                                <input type="text" wire:model="form.dsc_unidade_medida" class="form-control" placeholder="Ex: Percentual (%)">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-muted small text-uppercase fw-bold">Periodicidade</label>
                                                <select wire:model="form.dsc_periodo_medicao" class="form-select">
                                                    @foreach($periodosOptions as $per)
                                                        <option value="{{ $per }}">{{ $per }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-muted small text-uppercase fw-bold">É Acumulado?</label>
                                                <select wire:model="form.bln_acumulado" class="form-select">
                                                    <option value="Sim">Sim</option>
                                                    <option value="Não">Não</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-muted small text-uppercase fw-bold">Meta Descritiva</label>
                                                <input type="text" wire:model="form.dsc_meta" class="form-control" placeholder="Ex: Atingir 90%">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label text-muted small text-uppercase fw-bold">Fórmula de Cálculo</label>
                                                <textarea wire:model="form.dsc_formula" class="form-control" rows="2" placeholder="Ex: (Total A / Total B) * 100"></textarea>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label text-muted small text-uppercase fw-bold">Fonte dos Dados</label>
                                                <textarea wire:model="form.dsc_fonte" class="form-control" rows="2" placeholder="Ex: Relatórios do Sistema de Gestão X"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0 bg-light bg-opacity-50">
                        <button type="button" class="btn btn-light px-4" wire:click="$set('showModal', false)">Cancelar</button>
                        <button type="submit" class="btn btn-primary gradient-theme-btn px-4 py-2 fw-bold">
                            <i class="bi bi-save me-2"></i>Salvar Indicador
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Exclusão -->
    <div class="modal fade @if($showDeleteModal) show @endif" tabindex="-1" style="@if($showDeleteModal) display: block; background: rgba(0,0,0,0.5); @else display: none; @endif">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger text-white border-0">
                    <h5 class="modal-title fw-bold">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showDeleteModal', false)"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="mb-3 text-danger"><i class="bi bi-exclamation-triangle fs-1"></i></div>
                    <p class="fs-5 mb-0">Deseja realmente excluir este indicador?</p>
                    <p class="text-muted small">Todos os lançamentos de evolução e metas associadas serão perdidos.</p>
                </div>
                <div class="modal-footer border-0 p-4 justify-content-center">
                    <button type="button" class="btn btn-light px-4" wire:click="$set('showDeleteModal', false)">Cancelar</button>
                    <button type="button" class="btn btn-danger px-4" wire:click="delete">Sim, Excluir</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Metas -->
    <div class="modal fade @if($showMetasModal) show @endif" tabindex="-1" style="@if($showMetasModal) display: block; background: rgba(0,0,0,0.5); @else display: none; @endif">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header gradient-theme text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-bullseye me-2"></i>Metas Anuais</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showMetasModal', false)"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="small text-muted mb-4">Indicador: <strong>{{ $indicadorSelecionado?->nom_indicador }}</strong></p>
                    
                    <form wire:submit.prevent="salvarMeta" class="row g-2 mb-4 p-3 bg-light rounded-3 border">
                        <div class="col-md-4">
                            <label class="small fw-bold text-muted">Ano</label>
                            <input type="number" wire:model="metaAno" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-5">
                            <label class="small fw-bold text-muted">Meta ({{ $indicadorSelecionado?->dsc_unidade_medida }})</label>
                            <input type="number" step="0.01" wire:model="metaValor" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-sm btn-primary w-100">Adicionar</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover border">
                            <thead class="table-light">
                                <tr>
                                    <th>Ano</th>
                                    <th>Meta</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($indicadorSelecionado?->metasPorAno ?? [] as $meta)
                                    <tr>
                                        <td>{{ $meta->num_ano }}</td>
                                        <td>{{ number_format($meta->meta, 2, ',', '.') }}</td>
                                        <td class="text-end">
                                            <button wire:click="excluirMeta('{{ $meta->cod_meta_por_ano }}')" class="btn btn-sm text-danger p-0"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center py-3 text-muted small">Sem metas cadastradas.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Linha Base -->
    <div class="modal fade @if($showLinhaBaseModal) show @endif" tabindex="-1" style="@if($showLinhaBaseModal) display: block; background: rgba(0,0,0,0.5); @else display: none; @endif">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header gradient-theme text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-bar-chart-steps me-2"></i>Linha de Base</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showLinhaBaseModal', false)"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="small text-muted mb-4">Indicador: <strong>{{ $indicadorSelecionado?->nom_indicador }}</strong></p>
                    
                    <form wire:submit.prevent="salvarLinhaBase" class="row g-2 mb-4 p-3 bg-light rounded-3 border">
                        <div class="col-md-4">
                            <label class="small fw-bold text-muted">Ano</label>
                            <input type="number" wire:model="linhaBaseAno" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-5">
                            <label class="small fw-bold text-muted">Valor Base</label>
                            <input type="number" step="0.01" wire:model="linhaBaseValor" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-sm btn-primary w-100">Salvar</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover border">
                            <thead class="table-light">
                                <tr>
                                    <th>Ano</th>
                                    <th>Valor</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($indicadorSelecionado?->linhaBase ?? [] as $lb)
                                    <tr>
                                        <td>{{ $lb->num_ano }}</td>
                                        <td>{{ number_format($lb->num_linha_base, 2, ',', '.') }}</td>
                                        <td class="text-end">
                                            <button wire:click="excluirLinhaBase('{{ $lb->cod_linha_base }}')" class="btn btn-sm text-danger p-0"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center py-3 text-muted small">Sem linha de base.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .farol-dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            display: inline-block;
            border: 1px solid rgba(0,0,0,0.1);
        }
        .small-vinculo { font-size: 0.75rem; }
        .animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</div>