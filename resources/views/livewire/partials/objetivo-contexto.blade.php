{{--
    Partial: Contexto do Objetivo Estrategico

    Exibe informacoes contextuais completas para facilitar a compreensao do usuario:
    - PEI ativo
    - Perspectiva BSC
    - Objetivo Estrategico com metadados
    - KPIs resumidos

    Uso: @include('livewire.partials.objetivo-contexto', ['objetivo' => $objetivoFiltrado])
--}}

@if($objetivo)
    @php
        $perspectiva = $objetivo->perspectiva;
        $pei = $perspectiva?->pei;

        // Calcular KPIs
        $totalIndicadores = $objetivo->indicadores->count();
        $totalPlanos = $objetivo->planosAcao->count();
        $planosConcluidos = $objetivo->planosAcao->where('bln_status', 'Concluido')->count();
        $planosEmAndamento = $objetivo->planosAcao->where('bln_status', 'Em Andamento')->count();
        $planosAtrasados = $objetivo->planosAcao->filter(function($p) {
            return $p->dte_fim < now() && $p->bln_status !== 'Concluido';
        })->count();

        // Calcular media de atingimento dos indicadores
        $mediaAtingimento = 0;
        if ($totalIndicadores > 0) {
            $soma = 0;
            foreach ($objetivo->indicadores as $ind) {
                $soma += $ind->calcularAtingimento();
            }
            $mediaAtingimento = round($soma / $totalIndicadores, 1);
        }

        // Cores da perspectiva
        $coresPerspectiva = [
            1 => ['bg' => 'bg-secondary', 'text' => 'text-white'],
            2 => ['bg' => 'bg-success', 'text' => 'text-white'],
            3 => ['bg' => 'bg-info', 'text' => 'text-white'],
            4 => ['bg' => 'bg-warning', 'text' => 'text-dark'],
            5 => ['bg' => 'bg-primary', 'text' => 'text-white'],
        ];
        $cores = $coresPerspectiva[$perspectiva?->num_nivel_hierarquico_apresentacao ?? 1] ?? $coresPerspectiva[1];

        // Cor do farol baseado na media
        $corFarol = match(true) {
            $mediaAtingimento >= 100 => 'success',
            $mediaAtingimento >= 80 => 'primary',
            $mediaAtingimento >= 60 => 'info',
            $mediaAtingimento >= 40 => 'warning',
            default => 'danger'
        };
    @endphp

    <!-- Card de Contexto Hierarquico -->
    <div class="card border-0 shadow-sm mb-4 overflow-hidden">
        <!-- Header com Perspectiva -->
        <div class="card-header {{ $cores['bg'] }} {{ $cores['text'] }} py-2">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="opacity-75">
                        <i class="bi bi-diagram-3 me-1"></i>
                        {{ $pei?->dsc_pei ?? 'PEI' }} | Perspectiva {{ $perspectiva?->num_nivel_hierarquico_apresentacao ?? '' }}
                    </small>
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-layers me-2"></i>{{ $perspectiva?->dsc_perspectiva ?? 'Perspectiva' }}
                    </h6>
                </div>
                <a href="{{ route('pei.mapa') }}" class="btn btn-sm btn-light" wire:navigate title="Voltar ao Mapa Estrategico">
                    <i class="bi bi-map me-1"></i> Mapa
                </a>
            </div>
        </div>

        <!-- Corpo com Objetivo -->
        <div class="card-body">
            <!-- Objetivo Estrategico -->
            <div class="mb-3">
                <div class="d-flex align-items-start">
                    <div class="flex-shrink-0 me-3">
                        <div class="rounded-circle {{ $cores['bg'] }} {{ $cores['text'] }} d-flex align-items-center justify-content-center"
                             style="width: 48px; height: 48px; font-weight: bold;">
                            {{ $perspectiva?->num_nivel_hierarquico_apresentacao ?? '?' }}.{{ $objetivo->num_nivel_hierarquico_apresentacao ?? '?' }}
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="fw-bold mb-1">{{ $objetivo->nom_objetivo_estrategico }}</h5>
                        @if($objetivo->dsc_objetivo_estrategico)
                            <p class="text-muted mb-0 small">{{ $objetivo->dsc_objetivo_estrategico }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- KPIs em Cards -->
            <div class="row g-2">
                <!-- Total Indicadores -->
                <div class="col-6 col-md-3">
                    <div class="card h-100 border-0 bg-light">
                        <div class="card-body py-2 px-3 text-center">
                            <div class="fs-4 fw-bold text-primary">{{ $totalIndicadores }}</div>
                            <small class="text-muted">
                                <i class="bi bi-graph-up me-1"></i>Indicadores
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Media Atingimento -->
                <div class="col-6 col-md-3">
                    <div class="card h-100 border-0 bg-light">
                        <div class="card-body py-2 px-3 text-center">
                            <div class="fs-4 fw-bold text-{{ $corFarol }}">{{ $mediaAtingimento }}%</div>
                            <small class="text-muted">
                                <i class="bi bi-speedometer2 me-1"></i>Atingimento
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Total Planos -->
                <div class="col-6 col-md-3">
                    <div class="card h-100 border-0 bg-light">
                        <div class="card-body py-2 px-3 text-center">
                            <div class="fs-4 fw-bold text-info">{{ $totalPlanos }}</div>
                            <small class="text-muted">
                                <i class="bi bi-list-check me-1"></i>Planos
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Status dos Planos -->
                <div class="col-6 col-md-3">
                    <div class="card h-100 border-0 bg-light">
                        <div class="card-body py-2 px-3 text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <span class="badge bg-success" title="Concluidos">{{ $planosConcluidos }}</span>
                                <span class="badge bg-primary" title="Em Andamento">{{ $planosEmAndamento }}</span>
                                @if($planosAtrasados > 0)
                                    <span class="badge bg-danger" title="Atrasados">{{ $planosAtrasados }}</span>
                                @endif
                            </div>
                            <small class="text-muted">
                                <i class="bi bi-clipboard-check me-1"></i>Status
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer com navegacao rapida -->
        <div class="card-footer bg-light py-2">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    <i class="bi bi-clock me-1"></i>
                    Vigencia: {{ $pei?->num_ano_inicio_pei ?? '?' }} - {{ $pei?->num_ano_fim_pei ?? '?' }}
                </small>
                <div class="d-flex gap-2">
                    <a href="{{ route('indicadores.index', ['filtroObjetivo' => $objetivo->cod_objetivo_estrategico]) }}"
                       class="btn btn-sm btn-outline-primary {{ request()->routeIs('indicadores.*') ? 'active' : '' }}"
                       wire:navigate>
                        <i class="bi bi-graph-up me-1"></i>Indicadores
                    </a>
                    <a href="{{ route('planos.index', ['filtroObjetivo' => $objetivo->cod_objetivo_estrategico]) }}"
                       class="btn btn-sm btn-outline-info {{ request()->routeIs('planos.*') ? 'active' : '' }}"
                       wire:navigate>
                        <i class="bi bi-list-check me-1"></i>Planos
                    </a>
                </div>
            </div>
        </div>
    </div>
@endif
