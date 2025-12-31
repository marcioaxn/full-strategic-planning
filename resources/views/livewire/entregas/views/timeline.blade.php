{{-- View Timeline (Gantt Simplificado) --}}
@php
    // Calcular range de datas
    $hoje = now();
    $inicio = $hoje->copy()->subDays(7);
    $fim = $hoje->copy()->addDays(30);
    $dias = [];
    
    $current = $inicio->copy();
    while ($current <= $fim) {
        $dias[] = $current->copy();
        $current->addDay();
    }
    
    $totalDias = count($dias);
@endphp

<div class="notion-timeline">
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="notion-timeline-container">
            {{-- Cabeçalho com datas --}}
            <div class="notion-timeline-header">
                <div class="notion-timeline-label-col">Entrega</div>
                <div class="notion-timeline-dates">
                    @foreach($dias as $index => $dia)
                        <div class="notion-timeline-date {{ $dia->isToday() ? 'today' : '' }} {{ $dia->isWeekend() ? 'weekend' : '' }}">
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
                        // Calcular posição e largura da barra
                        $prazo = $entrega->dte_prazo;
                        $criacao = $entrega->created_at;
                        
                        // Se tem prazo, usar prazo como fim
                        // Se não, mostrar como ponto no dia de criação
                        $barraInicio = $criacao->greaterThan($inicio) ? $criacao : $inicio;
                        $barraFim = $prazo ?: $criacao->copy()->addDays(3);
                        
                        // Calcular offset e width em porcentagem
                        $offsetDias = max(0, $barraInicio->diffInDays($inicio));
                        $duracaoDias = max(1, $barraFim->diffInDays($barraInicio) + 1);
                        
                        $offsetPercent = ($offsetDias / $totalDias) * 100;
                        $widthPercent = min(($duracaoDias / $totalDias) * 100, 100 - $offsetPercent);
                        
                        $statusColor = $entrega->getStatusColor();
                    @endphp
                    
                    <div class="notion-timeline-row" wire:key="timeline-{{ $entrega->cod_entrega }}">
                        {{-- Label --}}
                        <div class="notion-timeline-label" wire:click="openDetails('{{ $entrega->cod_entrega }}')" title="{{ $entrega->dsc_entrega }}">
                            <span class="notion-status-dot" style="background-color: {{ $statusColor }}"></span>
                            <span class="text-truncate">{{ Str::limit($entrega->dsc_entrega, 25) }}</span>
                        </div>
                        
                        {{-- Grid de dias --}}
                        <div class="notion-timeline-grid">
                            @foreach($dias as $dia)
                                <div class="notion-timeline-cell {{ $dia->isToday() ? 'today' : '' }} {{ $dia->isWeekend() ? 'weekend' : '' }}"></div>
                            @endforeach
                            
                            {{-- Barra de progresso --}}
                            <div 
                                class="notion-timeline-bar {{ $entrega->isConcluida() ? 'completed' : '' }} {{ $entrega->isAtrasada() ? 'overdue' : '' }}"
                                style="left: {{ $offsetPercent }}%; width: {{ $widthPercent }}%; background-color: {{ $statusColor }};"
                                title="{{ $entrega->dsc_entrega }} | {{ $prazo ? 'Prazo: ' . $prazo->format('d/m/Y') : 'Sem prazo' }}"
                                wire:click="openDetails('{{ $entrega->cod_entrega }}')"
                            >
                                @if($widthPercent > 15)
                                    <span class="notion-timeline-bar-text">{{ Str::limit($entrega->dsc_entrega, 20) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="notion-timeline-empty">
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-bar-chart-steps fs-1 opacity-25"></i>
                            <p class="mb-0 mt-2">Nenhuma entrega para exibir na timeline.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Linha do dia atual --}}
            @php
                $todayOffset = $hoje->diffInDays($inicio);
                $todayPercent = ($todayOffset / $totalDias) * 100;
            @endphp
            <div class="notion-timeline-today-line" style="left: calc(180px + {{ $todayPercent }}%)"></div>
        </div>
    </div>

    {{-- Legenda --}}
    <div class="d-flex flex-wrap gap-3 mt-3 small text-muted">
        <div class="d-flex align-items-center gap-2">
            <div class="notion-timeline-legend-line today"></div>
            <span>Hoje</span>
        </div>
        @foreach(\App\Models\PEI\Entrega::STATUS_OPTIONS as $status)
            <div class="d-flex align-items-center gap-2">
                <div class="notion-timeline-legend-dot" style="background-color: {{ \App\Models\PEI\Entrega::STATUS_COLORS[$status] }}"></div>
                <span>{{ $status }}</span>
            </div>
        @endforeach
    </div>
</div>

<style>
    .notion-timeline-container {
        position: relative;
        min-height: 300px;
    }

    .notion-timeline-header {
        display: flex;
        border-bottom: 1px solid #e4e4e4;
        position: sticky;
        top: 0;
        background: white;
        z-index: 10;
    }

    .notion-timeline-label-col {
        width: 180px;
        min-width: 180px;
        padding: 12px;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        color: #9b9a97;
        border-right: 1px solid #e4e4e4;
    }

    .notion-timeline-dates {
        display: flex;
        flex: 1;
        overflow: hidden;
    }

    .notion-timeline-date {
        flex: 1;
        min-width: 30px;
        text-align: center;
        padding: 4px 0;
        font-size: 0.7rem;
        border-right: 1px solid #f0f0f0;
    }

    .notion-timeline-date.today {
        background: linear-gradient(180deg, #e8f4fd 0%, #fff 100%);
    }

    .notion-timeline-date.weekend {
        background: #f9f9f9;
    }

    .notion-timeline-day {
        display: block;
        color: #9b9a97;
        text-transform: uppercase;
        font-size: 0.6rem;
    }

    .notion-timeline-daynum {
        display: block;
        font-weight: 600;
        color: #37352f;
    }

    .notion-timeline-body {
        position: relative;
    }

    .notion-timeline-row {
        display: flex;
        border-bottom: 1px solid #f0f0f0;
        min-height: 40px;
    }

    .notion-timeline-row:hover {
        background: #fafafa;
    }

    .notion-timeline-label {
        width: 180px;
        min-width: 180px;
        padding: 8px 12px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.85rem;
        border-right: 1px solid #e4e4e4;
        cursor: pointer;
    }

    .notion-timeline-label:hover {
        background: #f5f5f5;
    }

    .notion-timeline-grid {
        display: flex;
        flex: 1;
        position: relative;
    }

    .notion-timeline-cell {
        flex: 1;
        min-width: 30px;
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
        height: 24px;
        border-radius: 4px;
        display: flex;
        align-items: center;
        padding: 0 8px;
        cursor: pointer;
        transition: all 0.15s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .notion-timeline-bar:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        transform: translateY(-50%) scale(1.02);
    }

    .notion-timeline-bar.completed {
        opacity: 0.6;
    }

    .notion-timeline-bar.overdue {
        border: 2px solid #e03e3e;
    }

    .notion-timeline-bar-text {
        font-size: 0.7rem;
        color: #37352f;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .notion-timeline-today-line {
        position: absolute;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #2563eb;
        z-index: 5;
    }

    .notion-timeline-legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
    }

    .notion-timeline-legend-line {
        width: 20px;
        height: 2px;
    }

    .notion-timeline-legend-line.today {
        background: #2563eb;
    }
</style>
