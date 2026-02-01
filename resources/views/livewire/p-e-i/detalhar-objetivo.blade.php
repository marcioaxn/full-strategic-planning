<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('objetivos.index') }}" wire:navigate class="text-decoration-none">Objetivos Estratégicos</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($objetivo->nom_objetivo, 30) }}</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold text-gray-800 mb-0">
                <i class="bi bi-crosshair me-2 text-primary"></i>Detalhes do Objetivo
            </h2>
            <p class="text-muted mb-0">
                {{ $objetivo->perspectiva->dsc_perspectiva }} • {{ $objetivo->perspectiva->pei->dsc_pei }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('objetivos.index') }}" wire:navigate class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Voltar
            </a>
            <button class="btn btn-primary gradient-theme">
                <i class="bi bi-pencil me-1"></i> Editar
            </button>
        </div>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="row g-4 mb-4">
        <!-- Atingimento -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-opacity-10 p-3 rounded-circle me-3" style="background-color: {{ $estatisticas['cor_farol'] ?? '#6c757d' }}20;">
                            <i class="bi bi-speedometer2 fs-4" style="color: {{ $estatisticas['cor_farol'] ?? '#6c757d' }}"></i>
                        </div>
                        <div>
                            <h6 class="card-subtitle text-muted mb-1">Atingimento</h6>
                            <h4 class="card-title mb-0">{{ number_format($estatisticas['atingimento'], 1) }}%</h4>
                        </div>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar" role="progressbar" style="width: {{ min(100, $estatisticas['atingimento']) }}%; background-color: {{ $estatisticas['cor_farol'] ?? '#0d6efd' }};"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Indicadores -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-circle bg-success bg-opacity-10 me-3">
                            <i class="bi bi-graph-up text-success fs-4"></i>
                        </div>
                        <div>
                            <h6 class="card-subtitle text-muted mb-1">KPIs</h6>
                            <h4 class="card-title mb-0">{{ $estatisticas['qtd_indicadores'] }}</h4>
                        </div>
                    </div>
                    <small class="text-muted">Indicadores vinculados</small>
                </div>
            </div>
        </div>

        <!-- Planos de Ação -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-circle bg-warning bg-opacity-10 me-3">
                            <i class="bi bi-kanban text-warning fs-4"></i>
                        </div>
                        <div>
                            <h6 class="card-subtitle text-muted mb-1">Planos de Ação</h6>
                            <h4 class="card-title mb-0">{{ $estatisticas['qtd_planos'] }}</h4>
                        </div>
                    </div>
                    <small class="text-muted">Iniciativas em andamento</small>
                </div>
            </div>
        </div>

        <!-- Riscos -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-circle bg-danger bg-opacity-10 me-3">
                            <i class="bi bi-exclamation-triangle text-danger fs-4"></i>
                        </div>
                        <div>
                            <h6 class="card-subtitle text-muted mb-1">Riscos</h6>
                            <h4 class="card-title mb-0">0</h4>
                        </div>
                    </div>
                    <small class="text-muted">Riscos monitorados</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Coluna Principal -->
        <div class="col-lg-8">
            <!-- Descrição e Futuro Almejado -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">{{ $objetivo->nom_objetivo }}</h5>
                    <p class="text-secondary mb-4">{{ $objetivo->dsc_objetivo }}</p>

                    @if($objetivo->futuroAlmejado->isNotEmpty())
                        <div class="p-3 bg-light rounded border-start border-4 border-info">
                            <h6 class="fw-bold text-info small text-uppercase mb-2">
                                <i class="bi bi-stars me-1"></i>Futuro Almejado
                            </h6>
                            @foreach($objetivo->futuroAlmejado as $futuro)
                                <p class="mb-0 text-muted fst-italic">{{ $futuro->dsc_futuro_almejado }}</p>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center p-3 border border-dashed rounded text-muted">
                            <small>Nenhum "Futuro Almejado" definido para este objetivo.</small>
                            <br>
                            <a href="#" class="btn btn-link btn-sm p-0">Definir agora</a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Indicadores -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold">Indicadores de Desempenho</h5>
                    <a href="{{ route('indicadores.index') }}" wire:navigate class="btn btn-sm btn-outline-primary">Gerenciar KPIs</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light text-muted text-uppercase" style="font-size: 0.7rem;">
                            <tr>
                                <th class="ps-4">Indicador</th>
                                <th class="text-center">Polaridade</th>
                                <th class="text-end">Previsto</th>
                                <th class="text-end">Realizado</th>
                                <th class="text-end pe-4">Atingimento</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($objetivo->indicadores as $indicador)
                                @php
                                    $atingimento = $indicador->calcularAtingimento();
                                    $corFarol = $indicador->getCorFarol();
                                    $ultimaEv = $indicador->getUltimaEvolucao();
                                    
                                    $polaridadeIcon = [
                                        'Positiva' => 'bi-arrow-up-circle-fill text-success',
                                        'Negativa' => 'bi-arrow-down-circle-fill text-danger',
                                        'Estabilidade' => 'bi-dash-circle-fill text-warning',
                                        'Não Aplicável' => 'bi-info-circle-fill text-muted'
                                    ][$indicador->dsc_polaridade ?? 'Positiva'] ?? 'bi-question-circle';
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $indicador->nom_indicador }}</div>
                                        <small class="text-muted">{{ $indicador->dsc_unidade_medida }}</small>
                                    </td>
                                    <td class="text-center">
                                        <i class="bi {{ $polaridadeIcon }} fs-5" title="{{ $indicador->dsc_polaridade ?? 'Positiva' }}"></i>
                                    </td>
                                    <td class="text-end">
                                        {{ $ultimaEv ? number_format($ultimaEv->vlr_previsto, 2, ',', '.') : '--' }}
                                    </td>
                                    <td class="text-end fw-bold">
                                        {{ $ultimaEv ? number_format($ultimaEv->vlr_realizado, 2, ',', '.') : '--' }}
                                    </td>
                                    <td class="text-end pe-4">
                                        @if($indicador->dsc_polaridade === 'Não Aplicável')
                                            <span class="badge bg-light text-muted border">Informativo</span>
                                        @else
                                            <div class="d-flex align-items-center justify-content-end gap-2">
                                                <span class="fw-bold" style="color: {{ $corFarol ?? '#6c757d' }};">@brazil_percent($atingimento, 1)</span>
                                                <div class="rounded-circle" style="width: 10px; height: 10px; background-color: {{ $corFarol ?? '#dee2e6' }};"></div>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        Nenhum indicador vinculado diretamente.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Planos de Ação -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold">Planos de Ação</h5>
                    <a href="{{ route('planos.index') }}" wire:navigate class="btn btn-sm btn-outline-primary">Gerenciar Planos</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Plano</th>
                                <th>Prazo</th>
                                <th>Orçamento</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($objetivo->planosAcao as $plano)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-medium">{{ $plano->dsc_plano_acao }}</div>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($plano->dte_fim)->format('d/m/Y') }}</td>
                                    <td>R$ {{ number_format($plano->val_orcamento, 2, ',', '.') }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $plano->bln_status }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        Nenhum plano de ação vinculado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Comentários e Colaboração -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">Colaboração e Comentários</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <textarea wire:model="novoComentario" class="form-control" rows="3" placeholder="Escreva um comentário ou sugestão sobre este objetivo..."></textarea>
                        <div class="d-flex justify-content-end mt-2">
                            <button wire:click="postarComentario" class="btn btn-primary btn-sm rounded-pill px-4">
                                <i class="bi bi-send me-1"></i> Postar
                            </button>
                        </div>
                    </div>

                    <div class="comments-list">
                        @forelse($objetivo->comentarios()->latest()->get() as $comment)
                            <div class="d-flex mb-3 gap-3 border-bottom pb-3">
                                <div class="avatar-circle bg-secondary bg-opacity-10 text-secondary" style="width: 36px; height: 36px; display:flex; align-items:center; justify-content:center; border-radius:50%; font-size:14px;">
                                    {{ substr($comment->user->name, 0, 2) }}
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <h6 class="fw-bold mb-0 small">{{ $comment->user->name }}</h6>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="text-muted x-small">{{ $comment->created_at->diffForHumans() }}</span>
                                            @if($comment->user_id === auth()->id() || auth()->user()->isSuperAdmin())
                                                <button wire:click="removerComentario('{{ $comment->cod_comentario }}')" class="btn btn-link text-danger p-0" title="Excluir">
                                                    <i class="bi bi-trash small"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                    <p class="small text-secondary mb-0">{{ $comment->dsc_comentario }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted">
                                <small>Nenhum comentário ainda. Seja o primeiro a colaborar!</small>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Coluna Direita -->
        <div class="col-lg-4">
            <!-- Contexto Estratégico -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light py-3">
                    <h6 class="card-title mb-0 fw-bold">Contexto Estratégico</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Perspectiva</small>
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle">
                            {{ $objetivo->perspectiva->dsc_perspectiva }}
                        </span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Nível Hierárquico</small>
                        <span class="fw-bold">{{ $objetivo->num_nivel_hierarquico_apresentacao }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
