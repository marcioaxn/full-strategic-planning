{{-- View Timeline/Gantt - Modo Funcional com Navegação e Interação --}}
@php
    use Carbon\Carbon;

    // Usa as propriedades do componente
    $inicio = Carbon::parse($timelineInicio);
    $fim = Carbon::parse($timelineFim);
    $hoje = now()->startOfDay();

    // Gera array de dias
    $dias = [];
    $current = $inicio->copy();
    while ($current <= $fim) {
        $dias[] = $current->copy();
        $current->addDay();
    }

    $totalDias = count($dias);

    // Agrupa dias por semana/mês para o cabeçalho
    $semanas = collect($dias)->groupBy(fn($d) => $d->format('W-Y'));
    $meses = collect($dias)->groupBy(fn($d) => $d->format('Y-m'));

    // Calcula estatísticas
    $entregasComPrazo = $entregas->filter(fn($e) => $e->dte_prazo)->count();
    $entregasNoPeriodo = $entregas->filter(function($e) use ($inicio, $fim) {
        if (!$e->dte_prazo) return false;
        return $e->dte_prazo->between($inicio, $fim);
    })->count();
@endphp

<div class="notion-timeline" x-data="ganttChart()" wire:key="timeline-{{ $timelineInicio }}-{{ $timelineFim }}">
    {{-- Barra de Controles --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                {{-- Navegação --}}
                <div class="btn-group btn-group-sm">
                    <button
                        wire:click="timelineAnterior"
                        class="btn btn-outline-secondary"
                        title="Período Anterior"
                    >
                        <i class="bi bi-chevron-double-left"></i>
                    </button>
                    <button
                        wire:click="timelineHoje"
                        class="btn btn-outline-primary"
                        title="Centralizar em Hoje"
                    >
                        <i class="bi bi-calendar-check me-1"></i>
                        Hoje
                    </button>
                    <button
                        wire:click="timelineProximo"
                        class="btn btn-outline-secondary"
                        title="Próximo Período"
                    >
                        <i class="bi bi-chevron-double-right"></i>
                    </button>
                </div>

                {{-- Período Atual --}}
                <div class="text-center">
                    <span class="badge bg-light text-dark border">
                        <i class="bi bi-calendar-range me-1"></i>
                        {{ $inicio->format('d/m/Y') }} - {{ $fim->format('d/m/Y') }}
                        <span class="text-muted ms-1">({{ $totalDias }} dias)</span>
                    </span>
                </div>

                {{-- Zoom --}}
                <div class="btn-group btn-group-sm">
                    <button
                        wire:click="timelineZoomIn"
                        class="btn btn-outline-secondary"
                        title="Zoom In (menos dias)"
                        {{ $totalDias <= 7 ? 'disabled' : '' }}
                    >
                        <i class="bi bi-zoom-in"></i>
                    </button>
                    <button
                        wire:click="timelineZoomOut"
                        class="btn btn-outline-secondary"
                        title="Zoom Out (mais dias)"
                        {{ $totalDias >= 120 ? 'disabled' : '' }}
                    >
                        <i class="bi bi-zoom-out"></i>
                    </button>
                </div>

                {{-- Estatísticas --}}
                <div class="d-flex gap-3 small text-muted">
                    <span title="Entregas no período visível">
                        <i class="bi bi-eye text-primary"></i>
                        {{ $entregasNoPeriodo }} visíveis
                    </span>
                    <span title="Total de entregas com prazo">
                        <i class="bi bi-calendar-check text-success"></i>
                        {{ $entregasComPrazo }} com prazo
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Grid do Gantt --}}
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="notion-timeline-container" style="overflow-x: auto;">
            {{-- Cabeçalho com Meses --}}
            <div class="notion-timeline-header-months">
                <div class="notion-timeline-label-col">
                    <span class="text-muted small">Entrega</span>
                </div>
                <div class="notion-timeline-months-row">
                    @foreach($meses as $mesKey => $diasDoMes)
                        @php
                            $mesCarbon = Carbon::parse($mesKey . '-01');
                            $largura = (count($diasDoMes) / $totalDias) * 100;
                        @endphp
                        <div class="notion-timeline-month" style="width: {{ $largura }}%;">
                            {{ $mesCarbon->translatedFormat('F Y') }}
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Cabeçalho com Dias --}}
            <div class="notion-timeline-header">
                <div class="notion-timeline-label-col"></div>
                <div class="notion-timeline-dates">
                    @foreach($dias as $index => $dia)
                        @php
                            $isHoje = $dia->isToday();
                            $isWeekend = $dia->isWeekend();
                            $isFirstOfMonth = $dia->day === 1;
                        @endphp
                        <div class="notion-timeline-date {{ $isHoje ? 'today' : '' }} {{ $isWeekend ? 'weekend' : '' }} {{ $isFirstOfMonth ? 'first-of-month' : '' }}"
                             title="{{ $dia->translatedFormat('l, d F Y') }}">
                            <span class="notion-timeline-day">{{ $dia->format('D') }}</span>
                            <span class="notion-timeline-daynum">{{ $dia->format('d') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Linhas de entregas --}}
            <div class="notion-timeline-body">
                @forelse($entregas as $entrega)
                    @php
                        $prazo = $entrega->dte_prazo;
                        $criacao = $entrega->created_at->startOfDay();

                        // Se tem prazo, usar prazo como fim. Se não, mostrar apenas no dia de criação
                        $barraInicio = $criacao->greaterThan($inicio) ? $criacao : $inicio;
                        $barraFim = $prazo ?: $criacao->copy()->addDays(1);

                        // Garante que a barra fim não seja antes da barra início
                        if ($barraFim->lessThan($barraInicio)) {
                            $barraFim = $barraInicio->copy()->addDay();
                        }

                        // Calcular offset e width em porcentagem
                        $offsetDias = max(0, $barraInicio->diffInDays($inicio, false));
                        $duracaoDias = max(1, $barraFim->diffInDays($barraInicio) + 1);

                        // Limita a barra ao período visível
                        if ($offsetDias < 0) {
                            $duracaoDias += $offsetDias;
                            $offsetDias = 0;
                        }
                        if ($offsetDias + $duracaoDias > $totalDias) {
                            $duracaoDias = $totalDias - $offsetDias;
                        }

                        $offsetPercent = ($offsetDias / $totalDias) * 100;
                        $widthPercent = max(2, min(($duracaoDias / $totalDias) * 100, 100 - $offsetPercent));

                        $statusColor = $entrega->getStatusColor();
                        $prioridadeInfo = $entrega->getPrioridadeInfo();

                        // Verifica se está fora do período visível
                        $foraDosPeriodo = ($prazo && ($prazo->lessThan($inicio) || $prazo->greaterThan($fim)));
                    @endphp

                    <div
                        class="notion-timeline-row {{ $entrega->isConcluida() ? 'completed' : '' }} {{ $entrega->isAtrasada() ? 'overdue' : '' }}"
                        wire:key="timeline-row-{{ $entrega->cod_entrega }}"
                    >
                        {{-- Label --}}
                        <div
                            class="notion-timeline-label"
                            wire:click="openDetails('{{ $entrega->cod_entrega }}')"
                            title="{{ $entrega->dsc_entrega }}"
                        >
                            <span class="notion-status-dot" style="background-color: {{ $statusColor }}"></span>
                            @if($prioridadeInfo)
                                <i class="bi bi-{{ $prioridadeInfo['icon'] }} me-1" style="color: {{ $prioridadeInfo['color'] }}; font-size: 0.7rem;" title="{{ $prioridadeInfo['label'] }}"></i>
                            @endif
                            <span class="text-truncate">{{ Str::limit($entrega->dsc_entrega, 22) }}</span>
                            @if($entrega->bln_arquivado)
                                <i class="bi bi-archive ms-1 text-muted" title="Arquivada"></i>
                            @endif
                        </div>

                        {{-- Grid de dias --}}
                        <div class="notion-timeline-grid">
                            @foreach($dias as $dia)
                                <div class="notion-timeline-cell {{ $dia->isToday() ? 'today' : '' }} {{ $dia->isWeekend() ? 'weekend' : '' }}"></div>
                            @endforeach

                            {{-- Barra de progresso (se estiver no período) --}}
                            @if(!$foraDosPeriodo && $widthPercent > 0)
                                <div
                                    class="notion-timeline-bar {{ $entrega->isConcluida() ? 'completed' : '' }} {{ $entrega->isAtrasada() ? 'overdue' : '' }}"
                                    style="left: {{ $offsetPercent }}%; width: {{ $widthPercent }}%; background-color: {{ $statusColor }};"
                                    wire:click="openDetails('{{ $entrega->cod_entrega }}')"
                                    x-data="{ dragging: false }"
                                    @mouseenter="showTooltip($event, '{{ addslashes($entrega->dsc_entrega) }}', '{{ $entrega->bln_status }}', '{{ $prazo ? $prazo->format('d/m/Y') : 'Sem prazo' }}')"
                                    @mouseleave="hideTooltip()"
                                >
                                    @if($widthPercent > 8)
                                        <span class="notion-timeline-bar-text">
                                            {{ Str::limit($entrega->dsc_entrega, (int)($widthPercent / 3)) }}
                                        </span>
                                    @endif

                                    {{-- Indicador de prazo no final da barra --}}
                                    @if($prazo && $widthPercent > 5)
                                        <span class="notion-timeline-bar-date">
                                            {{ $prazo->format('d/m') }}
                                        </span>
                                    @endif
                                </div>
                            @endif

                            {{-- Indicador de item fora do período --}}
                            @if($foraDosPeriodo && $prazo)
                                <div class="notion-timeline-outside">
                                    <i class="bi bi-arrow-{{ $prazo->lessThan($inicio) ? 'left' : 'right' }}"></i>
                                    {{ $prazo->format('d/m') }}
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="notion-timeline-empty">
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-bar-chart-steps fs-1 opacity-25"></i>
                            <p class="mb-0 mt-2">Nenhuma entrega para exibir na timeline.</p>
                            <small>Adicione entregas com prazos para visualizá-las aqui.</small>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Linha do dia atual --}}
            @if($hoje->between($inicio, $fim))
                @php
                    $todayOffset = $inicio->diffInDays($hoje);
                    $todayPercent = (($todayOffset + 0.5) / $totalDias) * 100;
                @endphp
                <div class="notion-timeline-today-line" style="left: calc(200px + {{ $todayPercent }}% - 1px);">
                    <div class="notion-timeline-today-marker">Hoje</div>
                </div>
            @endif
        </div>
    </div>

    {{-- Entregas sem prazo --}}
    @php
        $semPrazo = $entregas->filter(fn($e) => !$e->dte_prazo);
    @endphp

    @if($semPrazo->count() > 0)
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-warning bg-opacity-10 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-exclamation-triangle me-2 text-warning"></i>
                    Entregas sem prazo
                    <span class="badge bg-warning text-dark ms-2">{{ $semPrazo->count() }}</span>
                </h6>
                <small class="text-muted">Defina prazos para visualizar no Gantt</small>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    @foreach($semPrazo->take(8) as $entrega)
                        <div class="col-md-3">
                            <div
                                class="p-2 rounded border bg-light d-flex align-items-center gap-2 cursor-pointer"
                                wire:click="openDetails('{{ $entrega->cod_entrega }}')"
                                style="cursor: pointer;"
                            >
                                <span class="notion-status-dot" style="background-color: {{ $entrega->getStatusColor() }}"></span>
                                <span class="small text-truncate">{{ Str::limit($entrega->dsc_entrega, 30) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if($semPrazo->count() > 8)
                    <div class="text-center mt-2">
                        <small class="text-muted">e mais {{ $semPrazo->count() - 8 }} entregas sem prazo...</small>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Legenda --}}
    <div class="d-flex flex-wrap gap-4 mt-3 small text-muted justify-content-center">
        <div class="d-flex align-items-center gap-2">
            <div class="notion-timeline-legend-line today"></div>
            <span>Hoje</span>
        </div>
        @foreach(\App\Models\ActionPlan\Entrega::STATUS_OPTIONS as $status)
            <div class="d-flex align-items-center gap-2">
                <div class="notion-timeline-legend-dot" style="background-color: {{ \App\Models\ActionPlan\Entrega::STATUS_COLORS[$status] }}"></div>
                <span>{{ $status }}</span>
            </div>
        @endforeach
        <div class="d-flex align-items-center gap-2">
            <div class="notion-timeline-legend-dot overdue"></div>
            <span>Atrasada</span>
        </div>
    </div>

    {{-- Tooltip flutuante --}}
    <div
        x-show="tooltipVisible"
        x-transition
        class="notion-timeline-tooltip"
        :style="'top: ' + tooltipY + 'px; left: ' + tooltipX + 'px;'"
    >
        <div class="fw-bold" x-text="tooltipTitle"></div>
        <div class="small">
            <span class="text-muted">Status:</span> <span x-text="tooltipStatus"></span>
        </div>
        <div class="small">
            <span class="text-muted">Prazo:</span> <span x-text="tooltipPrazo"></span>
        </div>
    </div>
</div>

<script>
    function ganttChart() {
        return {
            tooltipVisible: false,
            tooltipX: 0,
            tooltipY: 0,
            tooltipTitle: '',
            tooltipStatus: '',
            tooltipPrazo: '',

            showTooltip(event, title, status, prazo) {
                this.tooltipTitle = title;
                this.tooltipStatus = status;
                this.tooltipPrazo = prazo;
                this.tooltipX = event.clientX + 15;
                this.tooltipY = event.clientY + 15;
                this.tooltipVisible = true;
            },

            hideTooltip() {
                this.tooltipVisible = false;
            }
        }
    }
</script>

<style>
    .notion-timeline-container {
        position: relative;
        min-height: 300px;
    }

    .notion-timeline-header-months {
        display: flex;
        border-bottom: 1px solid #e4e4e4;
        background: linear-gradient(180deg, #f8f9fa 0%, #fff 100%);
    }

    .notion-timeline-months-row {
        display: flex;
        flex: 1;
    }

    .notion-timeline-month {
        padding: 8px;
        font-weight: 700;
        font-size: 0.8rem;
        text-align: center;
        border-right: 1px solid #e4e4e4;
        color: var(--bs-primary);
        background: rgba(var(--bs-primary-rgb), 0.05);
    }

    .notion-timeline-month:last-child {
        border-right: none;
    }

    .notion-timeline-header {
        display: flex;
        border-bottom: 2px solid #e4e4e4;
        position: sticky;
        top: 0;
        background: white;
        z-index: 10;
    }

    .notion-timeline-label-col {
        width: 200px;
        min-width: 200px;
        padding: 10px 12px;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        color: #9b9a97;
        border-right: 2px solid #e4e4e4;
        background: #f8f9fa;
    }

    .notion-timeline-dates {
        display: flex;
        flex: 1;
        overflow: hidden;
    }

    .notion-timeline-date {
        flex: 1;
        min-width: 28px;
        text-align: center;
        padding: 4px 0;
        font-size: 0.65rem;
        border-right: 1px solid #f0f0f0;
        transition: background-color 0.15s ease;
    }

    .notion-timeline-date:hover {
        background-color: #f0f7ff;
    }

    .notion-timeline-date.today {
        background: linear-gradient(180deg, #dbeafe 0%, #fff 100%);
        border-left: 2px solid var(--bs-primary);
        border-right: 2px solid var(--bs-primary);
    }

    .notion-timeline-date.weekend {
        background: #fafafa;
    }

    .notion-timeline-date.first-of-month {
        border-left: 2px solid #dee2e6;
    }

    .notion-timeline-day {
        display: block;
        color: #9b9a97;
        text-transform: uppercase;
        font-size: 0.55rem;
        font-weight: 600;
    }

    .notion-timeline-daynum {
        display: block;
        font-weight: 700;
        color: #37352f;
        font-size: 0.75rem;
    }

    .notion-timeline-date.today .notion-timeline-daynum {
        color: var(--bs-primary);
    }

    .notion-timeline-body {
        position: relative;
    }

    .notion-timeline-row {
        display: flex;
        border-bottom: 1px solid #f0f0f0;
        min-height: 44px;
        transition: background-color 0.15s ease;
    }

    .notion-timeline-row:hover {
        background: #fafafa;
    }

    .notion-timeline-row.completed {
        opacity: 0.6;
    }

    .notion-timeline-row.overdue {
        background: rgba(220, 53, 69, 0.03);
    }

    .notion-timeline-label {
        width: 200px;
        min-width: 200px;
        padding: 8px 12px;
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.8rem;
        border-right: 2px solid #e4e4e4;
        cursor: pointer;
        background: #fafafa;
        transition: all 0.15s ease;
    }

    .notion-timeline-label:hover {
        background: #f0f7ff;
        color: var(--bs-primary);
    }

    .notion-status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .notion-timeline-grid {
        display: flex;
        flex: 1;
        position: relative;
    }

    .notion-timeline-cell {
        flex: 1;
        min-width: 28px;
        border-right: 1px solid #f8f8f8;
    }

    .notion-timeline-cell.today {
        background: rgba(37, 99, 235, 0.05);
    }

    .notion-timeline-cell.weekend {
        background: #fcfcfc;
    }

    .notion-timeline-bar {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        height: 26px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .notion-timeline-bar:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        transform: translateY(-50%) scale(1.02);
        z-index: 5;
    }

    .notion-timeline-bar.completed {
        opacity: 0.5;
    }

    .notion-timeline-bar.overdue {
        border: 2px solid #dc3545;
        animation: pulse-border 2s ease-in-out infinite;
    }

    @keyframes pulse-border {
        0%, 100% { border-color: #dc3545; }
        50% { border-color: rgba(220, 53, 69, 0.4); }
    }

    .notion-timeline-bar-text {
        font-size: 0.7rem;
        color: #1a202c;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-weight: 500;
    }

    .notion-timeline-bar-date {
        font-size: 0.6rem;
        color: rgba(0,0,0,0.5);
        font-weight: 600;
        background: rgba(255,255,255,0.7);
        padding: 1px 4px;
        border-radius: 3px;
        margin-left: 4px;
    }

    .notion-timeline-outside {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.65rem;
        color: #9b9a97;
        padding: 2px 8px;
        background: #f5f5f5;
        border-radius: 4px;
    }

    .notion-timeline-outside i {
        margin-right: 4px;
    }

    .notion-timeline-today-line {
        position: absolute;
        top: 0;
        bottom: 0;
        width: 2px;
        background: linear-gradient(180deg, var(--bs-primary) 0%, rgba(var(--bs-primary-rgb), 0.3) 100%);
        z-index: 15;
        pointer-events: none;
    }

    .notion-timeline-today-marker {
        position: absolute;
        top: -20px;
        left: 50%;
        transform: translateX(-50%);
        background: var(--bs-primary);
        color: white;
        font-size: 0.6rem;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 4px;
        white-space: nowrap;
    }

    .notion-timeline-legend-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
    }

    .notion-timeline-legend-dot.overdue {
        background: #dc3545;
        animation: pulse-border 2s ease-in-out infinite;
        border: 2px solid #dc3545;
    }

    .notion-timeline-legend-line {
        width: 24px;
        height: 3px;
        border-radius: 2px;
    }

    .notion-timeline-legend-line.today {
        background: linear-gradient(90deg, var(--bs-primary) 0%, rgba(var(--bs-primary-rgb), 0.3) 100%);
    }

    .notion-timeline-tooltip {
        position: fixed;
        z-index: 1000;
        background: white;
        border: 1px solid #e4e4e4;
        border-radius: 8px;
        padding: 10px 14px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        max-width: 300px;
        pointer-events: none;
    }

    .notion-timeline-empty {
        padding: 40px;
    }

    /* Dark mode */
    [data-bs-theme="dark"] .notion-timeline-header,
    [data-bs-theme="dark"] .notion-timeline-header-months {
        background: #2d3748;
    }

    [data-bs-theme="dark"] .notion-timeline-label-col,
    [data-bs-theme="dark"] .notion-timeline-label {
        background: #1a202c;
        border-color: #4a5568;
    }

    [data-bs-theme="dark"] .notion-timeline-date,
    [data-bs-theme="dark"] .notion-timeline-cell {
        border-color: #4a5568;
    }

    [data-bs-theme="dark"] .notion-timeline-daynum {
        color: #e2e8f0;
    }

    [data-bs-theme="dark"] .notion-timeline-bar-text {
        color: #1a202c;
    }

    [data-bs-theme="dark"] .notion-timeline-tooltip {
        background: #2d3748;
        border-color: #4a5568;
        color: #e2e8f0;
    }

    /* Responsivo */
    @media (max-width: 992px) {
        .notion-timeline-label-col {
            width: 150px;
            min-width: 150px;
        }

        .notion-timeline-label {
            width: 150px;
            min-width: 150px;
            font-size: 0.75rem;
        }

        .notion-timeline-date {
            min-width: 24px;
        }

        .notion-timeline-cell {
            min-width: 24px;
        }
    }

    @media (max-width: 768px) {
        .notion-timeline-label-col {
            width: 120px;
            min-width: 120px;
        }

        .notion-timeline-label {
            width: 120px;
            min-width: 120px;
            font-size: 0.7rem;
            padding: 6px 8px;
        }

        .notion-timeline-month {
            font-size: 0.7rem;
            padding: 6px;
        }

        .notion-timeline-day {
            display: none;
        }

        .notion-timeline-bar-text {
            display: none;
        }
    }
</style>
