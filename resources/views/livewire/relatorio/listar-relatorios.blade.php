<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-file-earmark-bar-graph me-2 text-primary"></i>{{ __('Relatórios') }}
            </h4>
            <p class="text-muted mb-0">
                {{ __('Geração de relatórios do Planejamento Estratégico') }}
            </p>
        </div>
    </div>

    @if($organizacaoNome)
        <div class="alert alert-info d-flex align-items-center mb-4 border-0 shadow-sm" role="alert">
            <i class="bi bi-building me-2 fs-5"></i>
            <div>
                <strong>{{ __('Organização:') }}</strong> {{ $organizacaoNome }}
                <span class="text-muted small ms-2">{{ __('(selecionada no menu superior)') }}</span>
            </div>
        </div>
    @else
        <div class="alert alert-warning d-flex align-items-center mb-4 border-0 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle me-2 fs-5"></i>
            <div>
                <strong>{{ __('Atenção:') }}</strong> {{ __('Selecione uma organização no menu superior para gerar relatórios específicos.') }}
            </div>
        </div>
    @endif

    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-light bg-opacity-50 border-bottom-0 pt-3 pb-2">
            <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-funnel me-2"></i>{{ __('Filtros de Geração') }}</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6 col-lg-4">
                    <label for="ano" class="form-label small fw-bold text-muted text-uppercase">{{ __('Ano de Referência') }}</label>
                    <select wire:model.live="anoSelecionado" id="ano" class="form-select form-select-sm">
                        @foreach($anos as $ano)
                            <option value="{{ $ano }}">{{ $ano }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 col-lg-4">
                    <label for="periodo" class="form-label small fw-bold text-muted text-uppercase">{{ __('Período') }}</label>
                    <select wire:model.live="periodoSelecionado" id="periodo" class="form-select form-select-sm">
                        <option value="anual">{{ __('Anual (Completo)') }}</option>
                        <option value="1_semestre">{{ __('1º Semestre') }}</option>
                        <option value="2_semestre">{{ __('2º Semestre') }}</option>
                        <option value="1_trimestre">{{ __('1º Trimestre') }}</option>
                        <option value="2_trimestre">{{ __('2º Trimestre') }}</option>
                        <option value="3_trimestre">{{ __('3º Trimestre') }}</option>
                        <option value="4_trimestre">{{ __('4º Trimestre') }}</option>
                    </select>
                </div>

                <div class="col-md-6 col-lg-4">
                    <label for="perspectiva" class="form-label small fw-bold text-muted text-uppercase">{{ __('Perspectiva') }}</label>
                    <select wire:model.live="perspectivaSelecionada" id="perspectiva" class="form-select form-select-sm">
                        <option value="">{{ __('Todas') }}</option>
                        @foreach($perspectivas as $persp)
                            <option value="{{ $persp->cod_perspectiva }}">{{ $persp->dsc_perspectiva }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Relatório de Identidade Estratégica -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-primary shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-clipboard-data me-2"></i>{{ __('Identidade Estratégica') }}
                    </h6>
                </div>
                <div class="card-body">
                    <p class="card-text text-muted small">
                        {{ __('Relatório completo com Missão, Visão e Valores da organização selecionada.') }}
                    </p>
                    <ul class="list-unstyled small text-muted mb-0">
                        <li><i class="bi bi-check text-success me-1"></i> {{ __('Missão institucional') }}</li>
                        <li><i class="bi bi-check text-success me-1"></i> {{ __('Visão de futuro') }}</li>
                        <li><i class="bi bi-check text-success me-1"></i> {{ __('Valores organizacionais') }}</li>
                    </ul>
                </div>
                <div class="card-footer bg-transparent border-top-0 pt-0 pb-3">
                    @if($organizacaoId)
                        <a href="{{ route('relatorios.identidade', ['organizacaoId' => $organizacaoId]) }}" class="btn btn-primary w-100 btn-sm shadow-sm" target="_blank">
                            <i class="bi bi-file-pdf me-1"></i> {{ __('Gerar PDF') }}
                        </a>
                    @else
                        <button class="btn btn-secondary w-100 btn-sm shadow-sm" disabled>
                            <i class="bi bi-exclamation-circle me-1"></i> {{ __('Selecione uma organização') }}
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Relatório Executivo -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-success shadow-sm">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-briefcase me-2"></i>{{ __('Relatório Executivo') }}
                    </h6>
                </div>
                <div class="card-body">
                    <p class="card-text text-muted small">
                        {{ __('Visão geral completa do planejamento estratégico para apresentação executiva.') }}
                    </p>
                    <ul class="list-unstyled small text-muted mb-0">
                        <li><i class="bi bi-check text-success me-1"></i> {{ __('Identidade estratégica') }}</li>
                        <li><i class="bi bi-check text-success me-1"></i> {{ __('Objetivos e indicadores') }}</li>
                        <li><i class="bi bi-check text-success me-1"></i> {{ __('Planos de ação') }}</li>
                    </ul>
                </div>
                <div class="card-footer bg-transparent border-top-0 pt-0 pb-3">
                    @if($organizacaoId)
                        <a href="{{ route('relatorios.executivo', ['organizacaoId' => $organizacaoId]) }}?ano={{ $anoSelecionado }}&periodo={{ $periodoSelecionado }}" class="btn btn-success w-100 btn-sm shadow-sm" target="_blank">
                            <i class="bi bi-file-pdf me-1"></i> {{ __('Gerar PDF') }}
                        </a>
                    @else
                        <button class="btn btn-secondary w-100 btn-sm shadow-sm" disabled>
                            <i class="bi bi-exclamation-circle me-1"></i> {{ __('Selecione uma organização') }}
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Relatório de Objetivos -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-info shadow-sm">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-bullseye me-2"></i>{{ __('Objetivos Estratégicos') }}
                    </h6>
                </div>
                <div class="card-body">
                    <p class="card-text text-muted small">
                        {{ __('Lista de todos os objetivos estratégicos organizados por perspectiva.') }}
                    </p>
                    <ul class="list-unstyled small text-muted mb-0">
                        <li><i class="bi bi-check text-success me-1"></i> {{ __('Objetivos por perspectiva') }}</li>
                        <li><i class="bi bi-check text-success me-1"></i> {{ __('Status atual') }}</li>
                        <li><i class="bi bi-check text-success me-1"></i> {{ __('Descrição detalhada') }}</li>
                    </ul>
                </div>
                <div class="card-footer bg-transparent border-top-0 pt-0 pb-3">
                    <div class="btn-group w-100">
                        <a href="{{ route('relatorios.objetivos.pdf') }}?organizacao_id={{ $organizacaoId }}&ano={{ $anoSelecionado }}&perspectiva={{ $perspectivaSelecionada }}" class="btn btn-info btn-sm text-white shadow-sm" target="_blank">
                            <i class="bi bi-file-pdf me-1"></i> PDF
                        </a>
                        <a href="{{ route('relatorios.objetivos.excel') }}?organizacao_id={{ $organizacaoId }}&ano={{ $anoSelecionado }}&perspectiva={{ $perspectivaSelecionada }}" class="btn btn-outline-info btn-sm shadow-sm">
                            <i class="bi bi-file-excel me-1"></i> Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Relatório de Indicadores -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-warning shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="bi bi-graph-up me-2"></i>{{ __('Indicadores de Desempenho') }}
                    </h6>
                </div>
                <div class="card-body">
                    <p class="card-text text-muted small">
                        {{ __('Indicadores de desempenho (KPIs) com metas e resultados.') }}
                    </p>
                    <ul class="list-unstyled small text-muted mb-0">
                        <li><i class="bi bi-check text-success me-1"></i> {{ __('Métricas e metas') }}</li>
                        <li><i class="bi bi-check text-success me-1"></i> {{ __('Valores realizados') }}</li>
                        <li><i class="bi bi-check text-success me-1"></i> {{ __('Análise de desempenho') }}</li>
                    </ul>
                </div>
                <div class="card-footer bg-transparent border-top-0 pt-0 pb-3">
                    <div class="btn-group w-100">
                        <a href="{{ route('relatorios.indicadores.pdf', ['organizacaoId' => $organizacaoId]) }}?ano={{ $anoSelecionado }}&periodo={{ $periodoSelecionado }}" class="btn btn-warning btn-sm text-dark shadow-sm" target="_blank">
                            <i class="bi bi-file-pdf me-1"></i> PDF
                        </a>
                        <a href="{{ route('relatorios.indicadores.excel', ['organizacaoId' => $organizacaoId]) }}?ano={{ $anoSelecionado }}&periodo={{ $periodoSelecionado }}" class="btn btn-outline-warning btn-sm shadow-sm">
                            <i class="bi bi-file-excel me-1"></i> Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Relatório de Planos de Ação -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-secondary shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-list-task me-2"></i>{{ __('Planos de Ação') }}
                    </h6>
                </div>
                <div class="card-body">
                    <p class="card-text text-muted small">
                        {{ __('Acompanhamento detalhado dos planos de ação e iniciativas.') }}
                    </p>
                    <ul class="list-unstyled small text-muted mb-0">
                        <li><i class="bi bi-check text-success me-1"></i> {{ __('Status de execução') }}</li>
                        <li><i class="bi bi-check text-success me-1"></i> {{ __('Prazos e responsáveis') }}</li>
                        <li><i class="bi bi-check text-success me-1"></i> {{ __('Progresso físico/financeiro') }}</li>
                    </ul>
                </div>
                <div class="card-footer bg-transparent border-top-0 pt-0 pb-3">
                    <div class="btn-group w-100">
                        <a href="{{ route('relatorios.planos.pdf') }}?organizacao_id={{ $organizacaoId }}&ano={{ $anoSelecionado }}" class="btn btn-secondary btn-sm shadow-sm" target="_blank">
                            <i class="bi bi-file-pdf me-1"></i> PDF
                        </a>
                        <a href="{{ route('relatorios.planos.excel') }}?organizacao_id={{ $organizacaoId }}&ano={{ $anoSelecionado }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                            <i class="bi bi-file-excel me-1"></i> Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Relatório de Riscos -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-danger shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>{{ __('Gestão de Riscos') }}
                    </h6>
                </div>
                <div class="card-body">
                    <p class="card-text text-muted small">
                        {{ __('Matriz de riscos e plano de mitigação.') }}
                    </p>
                    <ul class="list-unstyled small text-muted mb-0">
                        <li><i class="bi bi-check text-success me-1"></i> {{ __('Matriz de Probabilidade x Impacto') }}</li>
                        <li><i class="bi bi-check text-success me-1"></i> {{ __('Ações de mitigação') }}</li>
                        <li><i class="bi bi-check text-success me-1"></i> {{ __('Monitoramento de riscos') }}</li>
                    </ul>
                </div>
                <div class="card-footer bg-transparent border-top-0 pt-0 pb-3">
                    <div class="btn-group w-100">
                        <a href="{{ route('relatorios.riscos.pdf') }}?organizacao_id={{ $organizacaoId }}" class="btn btn-danger btn-sm shadow-sm" target="_blank">
                            <i class="bi bi-file-pdf me-1"></i> PDF
                        </a>
                        <a href="{{ route('relatorios.riscos.excel') }}?organizacao_id={{ $organizacaoId }}" class="btn btn-outline-danger btn-sm shadow-sm">
                            <i class="bi bi-file-excel me-1"></i> Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>