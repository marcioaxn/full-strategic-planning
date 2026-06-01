<div>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('planos.index') }}" class="text-decoration-none">Planos de Ação</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Detalhes</li>
                    </ol>
                </nav>
                <h2 class="h4 fw-bold mb-0">Ficha Técnica do Plano</h2>
                <div class="d-flex gap-2 mt-1">
                    <x-projetos-link :page="23" label="TAP/Kick-off" />
                    <x-projetos-link :page="36" label="EAP" />
                    <x-projetos-link :page="89" label="RACI" />
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('planos.entregas', $plano->cod_plano_de_acao) }}" class="btn btn-outline-info rounded-pill px-3">
                    <i class="bi bi-list-check me-1"></i> Entregas
                </a>
                <a href="{{ route('planos.responsaveis', $plano->cod_plano_de_acao) }}" class="btn btn-outline-warning rounded-pill px-3">
                    <i class="bi bi-people me-1"></i> Gestores
                </a>
            </div>
        </div>
    </x-slot>

    <div class="row g-4">
        <!-- Coluna Esquerda: Geral e Entregas -->
        <div class="col-lg-8">
            <!-- Informações Gerais -->
            <div class="card border-0 shadow-sm mb-4 overflow-hidden">
                <div class="card-header gradient-theme text-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2"></i>Informações Gerais</h5>
                        <span class="badge bg-white text-primary rounded-pill px-3">{{ $plano->bln_status }}</span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <h3 class="fw-bold mb-3">{{ $plano->dsc_plano_de_acao }}</h3>
                    
                    @if($plano->txt_detalhamento)
                        <div class="mb-4 p-3 bg-light rounded border-start border-4 border-info shadow-sm">
                            <label class="text-muted small text-uppercase fw-bold d-block mb-2">
                                <i class="bi bi-justify-left me-1"></i>Detalhamento / Justificativa
                            </label>
                            <div class="text-dark lh-base" style="white-space: pre-line;">{{ $plano->txt_detalhamento }}</div>
                        </div>
                    @endif

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase fw-bold d-block">Objetivo</label>
                            <p class="mb-0 fw-semibold">
                                <i class="bi bi-bullseye text-primary me-2"></i>
                                {{ $plano->objetivo->nom_objetivo ?? 'N/A' }}
                            </p>
                            <small class="text-muted ps-4">{{ $plano->objetivo->perspectiva->dsc_perspectiva ?? '' }}</small>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted small text-uppercase fw-bold d-block">Tipo</label>
                            <p class="mb-0">{{ $plano->tipoExecucao->dsc_tipo_execucao ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted small text-uppercase fw-bold d-block">Organização</label>
                            <p class="mb-0">{{ $plano->organizacao->sgl_organizacao ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="row g-4 pt-3 border-top">
                        <div class="col-md-3">
                            <label class="text-muted small text-uppercase fw-bold d-block">Início</label>
                            <p class="mb-0 fw-bold"><i class="bi bi-calendar-check me-2"></i>{{ $plano->dte_inicio?->format('d/m/Y') }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted small text-uppercase fw-bold d-block">Término</label>
                            <p class="mb-0 fw-bold {{ $plano->isAtrasado() ? 'text-danger' : '' }}">
                                <i class="bi bi-calendar-x me-2"></i>{{ $plano->dte_fim?->format('d/m/Y') }}
                            </p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted small text-uppercase fw-bold d-block">Orçamento</label>
                            <p class="mb-0 fw-mono">R$ @brazil_number($plano->vlr_orcamento_previsto, 2)</p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted small text-uppercase fw-bold d-block">Vínculos Orç.</label>
                            <small class="d-block">PPA: {{ $plano->cod_ppa ?: '-' }}</small>
                            <small class="d-block">LOA: {{ $plano->cod_loa ?: '-' }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progresso e Entregas -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-check2-all me-2 text-primary"></i>Status de Execução</h5>
                </div>
                <div class="card-body p-4 pt-0">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-muted">Progresso via Entregas</span>
                            <span class="fw-bold text-primary">@brazil_percent($progresso, 1)</span>
                        </div>
                        <div class="progress rounded-pill" style="height: 10px;">
                            <div class="progress-bar gradient-theme" style="width: {{ $progresso }}%"></div>
                        </div>
                    </div>

                    <div class="list-group list-group-flush border-top">
                        @forelse($plano->entregas->sortBy('num_nivel_hierarquico_apresentacao') as $entrega)
                            <div class="list-group-item px-0 py-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="d-flex">
                                        @if($entrega->bln_status === 'Concluído')
                                            <i class="bi bi-check-circle-fill text-success me-3 fs-5"></i>
                                        @else
                                            <i class="bi bi-circle text-muted me-3 fs-5"></i>
                                        @endif
                                        <div>
                                            <span class="fw-semibold {{ $entrega->bln_status === 'Concluído' ? 'text-decoration-line-through text-muted' : '' }}">
                                                {{ $entrega->dsc_entrega }}
                                            </span>
                                            <small class="d-block text-muted mt-1">{{ $entrega->dsc_periodo_medicao }}</small>
                                        </div>
                                    </div>
                                    <span class="badge bg-light text-dark border rounded-pill">{{ $entrega->bln_status }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-center py-4 text-muted">Nenhuma entrega cadastrada.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Coluna Direita: Responsáveis e Auditoria -->
        <div class="col-lg-4">
            <!-- Responsáveis -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-people me-2 text-primary"></i>Equipe Responsável</h5>
                </div>
                <div class="card-body p-4 pt-0">
                    <ul class="list-unstyled mb-0">
                        @forelse($responsaveis as $resp)
                            <li class="d-flex align-items-center mb-3">
                                <div class="avatar-sm-det me-3 gradient-theme text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                    {{ substr($resp->name, 0, 1) }}
                                </div>
                                <div>
                                    <span class="fw-semibold d-block small">{{ $resp->name }}</span>
                                    <span class="badge bg-light text-muted border py-1 px-2" style="font-size: 0.65rem;">{{ $resp->dsc_perfil }}</span>
                                </div>
                            </li>
                        @empty
                            <p class="text-muted small text-center py-2">Sem responsáveis atribuídos.</p>
                        @endforelse
                    </ul>
                </div>
            </div>

            <!-- Histórico de Alterações -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i>Histórico</h5>
                </div>
                <div class="card-body p-4 pt-0">
                    <div class="timeline-det">
                        @forelse($auditoria as $audit)
                            <div class="timeline-item-det pb-3 mb-3 border-bottom">
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="fw-bold">{{ $audit->user->name ?? 'Sistema' }}</small>
                                    <small class="text-muted">{{ $audit->created_at->format('d/m/Y H:i') }}</small>
                                </div>
                                <div class="d-flex align-items-center">
                                    @php
                                        $eventClass = match($audit->event) {
                                            'created' => 'success',
                                            'updated' => 'primary',
                                            'deleted' => 'danger',
                                            default => 'secondary'
                                        };
                                        $eventLabel = match($audit->event) {
                                            'created' => 'Criação',
                                            'updated' => 'Atualização',
                                            'deleted' => 'Exclusão',
                                            default => $audit->event
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $eventClass }} bg-opacity-10 text-{{ $eventClass }} small py-0 px-2 me-2">{{ $eventLabel }}</span>
                                    <small class="text-muted">ID: ...{{ substr($audit->id, -6) }}</small>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted small text-center">Sem histórico registrado.</p>
                        @endforelse
                    </div>
                    @if($auditoria->isNotEmpty())
                        <div class="text-center mt-3">
                            <a href="{{ route('audit.index', ['filtroModel' => 'PlanoDeAcao', 'filtroId' => $plano->cod_plano_de_acao]) }}" 
                               wire:navigate 
                               class="btn btn-link btn-sm text-decoration-none">
                                <i class="bi bi-clock-history me-1"></i>Ver histórico completo
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Modelo Lógico --}}
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-3 px-4">
            <div>
                <h6 class="fw-bold mb-0"><i class="bi bi-diagram-3 me-2 text-primary"></i>Modelo Lógico</h6>
                <div class="mt-1"><x-gppei-link :page="86" label="Modelo Lógico" /></div>
            </div>
        </div>
        <div class="card-body p-4">
            @php $ml = $plano->json_modelo_logico ?? []; @endphp
            @if(empty(array_filter($ml)))
                <div class="text-center py-3 text-muted">
                    <i class="bi bi-diagram-3 fs-1 opacity-25 d-block mb-2"></i>
                    <p class="small mb-1">Modelo Lógico não preenchido.</p>
                    <p class="x-small text-muted mb-0">Edite o plano de ação para preencher Insumos, Atividades, Resultados e Impacto.</p>
                </div>
            @else
                <div class="row g-3 align-items-stretch">
                    @foreach(['insumos' => ['icon' => 'box-seam', 'label' => 'Insumos', 'color' => 'primary'], 'atividades' => ['icon' => 'tools', 'label' => 'Atividades', 'color' => 'info'], 'resultados' => ['icon' => 'graph-up-arrow', 'label' => 'Resultados', 'color' => 'success'], 'impacto' => ['icon' => 'star-fill', 'label' => 'Impacto', 'color' => 'warning'], 'pressupostos' => ['icon' => 'shield-check', 'label' => 'Pressupostos/Riscos', 'color' => 'danger']] as $k => $meta)
                    @if(!empty($ml[$k]))
                    <div class="col-md">
                        <div class="card border-0 bg-{{ $meta['color'] }}-subtle h-100">
                            <div class="card-body p-3">
                                <p class="fw-bold small text-{{ $meta['color'] }} text-uppercase mb-2">
                                    <i class="bi bi-{{ $meta['icon'] }} me-1"></i>{{ $meta['label'] }}
                                </p>
                                <p class="small text-dark mb-0" style="white-space:pre-line;">{{ $ml[$k] }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Matriz RACI --}}
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-3 px-4">
            <div>
                <h6 class="fw-bold mb-0"><i class="bi bi-people me-2 text-warning"></i>Matriz RACI</h6>
                <div class="mt-1 d-flex gap-2">
                    <x-gppei-link :page="120" label="RACI GPPEI" />
                    <x-projetos-link :page="89" label="Matriz RACI" />
                </div>
            </div>
            <a href="{{ route('planos.responsaveis', $plano->cod_plano_de_acao) }}" wire:navigate
               class="btn btn-sm btn-outline-warning rounded-pill px-3">
                <i class="bi bi-people me-1"></i>Gerenciar Responsáveis
            </a>
        </div>
        <div class="card-body p-4">
            @php
                try {
                    $racis = \App\Models\ActionPlan\Raci::with('usuario')
                        ->where('cod_plano_de_acao', $plano->cod_plano_de_acao)
                        ->get()
                        ->groupBy('dsc_papel');
                } catch (\Exception $e) {
                    $racis = collect();
                }
                $papeis = ['R' => ['label' => 'R — Responsável', 'color' => 'danger', 'desc' => 'executa'], 'A' => ['label' => 'A — Aprovador', 'color' => 'warning', 'desc' => 'accountability'], 'C' => ['label' => 'C — Consultado', 'color' => 'info', 'desc' => 'contribui'], 'I' => ['label' => 'I — Informado', 'color' => 'secondary', 'desc' => 'recebe resultado']];
            @endphp
            @if($racis->isEmpty())
                <div class="text-center py-3 text-muted small">
                    <i class="bi bi-people fs-1 opacity-25 d-block mb-2"></i>
                    <p class="mb-0">Nenhum papel RACI definido. Use "Gerenciar Responsáveis" para atribuir papéis.</p>
                </div>
            @else
                <div class="row g-3">
                    @foreach($papeis as $papel => $meta)
                    @if($racis->has($papel))
                    <div class="col-md-6 col-lg-3">
                        <div class="card border-{{ $meta['color'] }} border-opacity-25 h-100">
                            <div class="card-header bg-{{ $meta['color'] }}-subtle py-2 px-3 border-0">
                                <span class="fw-bold small text-{{ $meta['color'] }}">{{ $meta['label'] }}</span>
                                <small class="text-muted d-block" style="font-size:.65rem;">{{ $meta['desc'] }}</small>
                            </div>
                            <div class="card-body p-2">
                                @foreach($racis[$papel] as $raci)
                                    <div class="d-flex align-items-center gap-2 py-1">
                                        <i class="bi bi-person-circle text-{{ $meta['color'] }} flex-shrink-0"></i>
                                        <span class="small">{{ $raci->usuario?->name ?? '—' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Plano de Comunicação --}}
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-3 px-4">
            <div>
                <h6 class="fw-bold mb-0"><i class="bi bi-megaphone me-2 text-info"></i>Plano de Comunicação</h6>
                <div class="mt-1"><x-projetos-link :page="143" label="Domínio 5 — Comunicação" /></div>
            </div>
        </div>
        <div class="card-body p-0">
            @if($comunicacoes->isEmpty())
                <div class="text-center py-4 text-muted small">
                    <i class="bi bi-megaphone fs-1 opacity-25 d-block mb-2"></i>
                    Nenhum item de comunicação definido. Acesse "Gerenciar Responsáveis" para planejar a comunicação do projeto.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light text-muted text-uppercase">
                            <tr>
                                <th class="ps-4">Público-Alvo</th>
                                <th>Mensagem-Chave</th>
                                <th>Canal</th>
                                <th>Frequência</th>
                                <th>Responsável</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($comunicacoes as $com)
                            <tr>
                                <td class="ps-4 fw-semibold">{{ $com->nom_publico_alvo }}</td>
                                <td class="text-muted">{{ Str::limit($com->dsc_mensagem_chave, 60) }}</td>
                                <td><span class="badge bg-info-subtle text-info">{{ $com->dsc_canal }}</span></td>
                                <td>{{ $com->dsc_frequencia }}</td>
                                <td>{{ $com->nom_responsavel ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Lições Aprendidas do Plano --}}
    @if($licoes->isNotEmpty())
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-3 px-4">
            <div>
                <h6 class="fw-bold mb-0"><i class="bi bi-lightbulb me-2 text-warning"></i>Lições Aprendidas</h6>
                <div class="mt-1"><x-projetos-link :page="227" label="Domínio 7 — Impacto e Aprendizado" /></div>
            </div>
            <a href="{{ route('licoes.index') }}" wire:navigate class="btn btn-sm btn-outline-warning rounded-pill px-3">
                Ver Todas
            </a>
        </div>
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                @foreach($licoes->take(5) as $licao)
                @php $tiposMeta = \App\Models\ActionPlan\LicaoAprendida::TIPOS; $meta = $tiposMeta[$licao->dsc_tipo] ?? ['icon' => 'lightbulb', 'color' => 'secondary']; @endphp
                <div class="list-group-item px-4 py-2">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-{{ $meta['icon'] }} text-{{ $meta['color'] }} flex-shrink-0 mt-1" style="font-size:.8rem;"></i>
                        <div>
                            <span class="badge bg-secondary-subtle text-secondary" style="font-size:.65rem;">{{ $licao->dsc_categoria }}</span>
                            <p class="small mb-0 mt-1">{{ Str::limit($licao->txt_descricao, 100) }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <style>
        .italic { font-style: italic; }
        .fw-mono { font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
        .timeline-item-det:last-child { border-bottom: 0 !important; margin-bottom: 0 !important; }
    </style>
</div>
