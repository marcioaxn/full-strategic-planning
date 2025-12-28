{{-- View Calendário --}}
@php
    $hoje = now();
    $mesAtual = $hoje->copy()->startOfMonth();
    $primeiroDia = $mesAtual->copy()->startOfWeek();
    $ultimoDia = $mesAtual->copy()->endOfMonth()->endOfWeek();
    
    $semanas = [];
    $diaAtual = $primeiroDia->copy();
    
    while ($diaAtual <= $ultimoDia) {
        $semana = [];
        for ($i = 0; $i < 7; $i++) {
            $semana[] = $diaAtual->copy();
            $diaAtual->addDay();
        }
        $semanas[] = $semana;
    }
    
    // Agrupar entregas por data de prazo
    $entregasPorData = $entregas->filter(fn($e) => $e->dte_prazo)->groupBy(fn($e) => $e->dte_prazo->format('Y-m-d'));
@endphp

<div class="notion-calendario">
    <div class="card border-0 shadow-sm overflow-hidden">
        {{-- Navegação do mês --}}
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
            <button class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-chevron-left"></i>
            </button>
            <h5 class="mb-0 fw-bold">{{ $mesAtual->translatedFormat('F Y') }}</h5>
            <button class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>

        {{-- Grid do calendário --}}
        <div class="notion-calendario-grid">
            {{-- Cabeçalho dos dias da semana --}}
            <div class="notion-calendario-header">
                @foreach(['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'] as $diaSemana)
                    <div class="notion-calendario-weekday">{{ $diaSemana }}</div>
                @endforeach
            </div>

            {{-- Semanas --}}
            @foreach($semanas as $semana)
                <div class="notion-calendario-week">
                    @foreach($semana as $dia)
                        @php
                            $diaKey = $dia->format('Y-m-d');
                            $entregasDoDia = $entregasPorData[$diaKey] ?? collect();
                            $isMesAtual = $dia->month === $mesAtual->month;
                            $isHoje = $dia->isToday();
                            $isPassado = $dia->isPast() && !$isHoje;
                        @endphp
                        
                        <div 
                            class="notion-calendario-day {{ !$isMesAtual ? 'other-month' : '' }} {{ $isHoje ? 'today' : '' }} {{ $dia->isWeekend() ? 'weekend' : '' }}"
                            @can('update', $plano)
                                wire:click="openQuickAdd('Não Iniciado')"
                            @endcan
                        >
                            <div class="notion-calendario-daynum {{ $isHoje ? 'active' : '' }}">
                                {{ $dia->format('d') }}
                            </div>
                            
                            <div class="notion-calendario-events">
                                @foreach($entregasDoDia->take(3) as $entrega)
                                    <div 
                                        class="notion-calendario-event {{ $entrega->isConcluida() ? 'completed' : '' }} {{ $isPassado && !$entrega->isConcluida() ? 'overdue' : '' }}"
                                        style="background-color: {{ $entrega->getStatusColor() }};"
                                        wire:click.stop="openDetails('{{ $entrega->cod_entrega }}')"
                                        title="{{ $entrega->dsc_entrega }}"
                                    >
                                        <span class="notion-calendario-event-text">
                                            {{ Str::limit($entrega->dsc_entrega, 15) }}
                                        </span>
                                    </div>
                                @endforeach
                                
                                @if($entregasDoDia->count() > 3)
                                    <div class="notion-calendario-more">
                                        +{{ $entregasDoDia->count() - 3 }} mais
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>

    {{-- Entregas sem prazo --}}
    @php
        $semPrazo = $entregas->filter(fn($e) => !$e->dte_prazo);
    @endphp
    
    @if($semPrazo->count() > 0)
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-light border-bottom">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-calendar-x me-2 text-muted"></i>
                    Entregas sem prazo definido
                    <span class="badge bg-secondary ms-2">{{ $semPrazo->count() }}</span>
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="d-flex flex-wrap gap-2 p-3">
                    @foreach($semPrazo->take(10) as $entrega)
                        <div 
                            class="notion-calendario-chip"
                            style="background-color: {{ $entrega->getStatusColor() }}80;"
                            wire:click="openDetails('{{ $entrega->cod_entrega }}')"
                        >
                            {{ Str::limit($entrega->dsc_entrega, 30) }}
                        </div>
                    @endforeach
                    @if($semPrazo->count() > 10)
                        <span class="text-muted small align-self-center">
                            e mais {{ $semPrazo->count() - 10 }} entregas...
                        </span>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

<style>
    .notion-calendario-grid {
        display: flex;
        flex-direction: column;
    }

    .notion-calendario-header {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        border-bottom: 1px solid #e4e4e4;
    }

    .notion-calendario-weekday {
        padding: 12px;
        text-align: center;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        color: #9b9a97;
    }

    .notion-calendario-week {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
    }

    .notion-calendario-day {
        min-height: 100px;
        border-right: 1px solid #f0f0f0;
        border-bottom: 1px solid #f0f0f0;
        padding: 4px;
        cursor: pointer;
        transition: background-color 0.15s ease;
    }

    .notion-calendario-day:hover {
        background-color: #f7f7f5;
    }

    .notion-calendario-day:nth-child(7n) {
        border-right: none;
    }

    .notion-calendario-day.other-month {
        background-color: #fafafa;
    }

    .notion-calendario-day.other-month .notion-calendario-daynum {
        color: #d4d4d4;
    }

    .notion-calendario-day.weekend {
        background-color: #fefefe;
    }

    .notion-calendario-day.today {
        background-color: #f0f7ff;
    }

    .notion-calendario-daynum {
        font-size: 0.8rem;
        font-weight: 500;
        color: #37352f;
        padding: 4px 8px;
    }

    .notion-calendario-daynum.active {
        background: var(--bs-primary);
        color: white;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        padding: 0;
    }

    .notion-calendario-events {
        display: flex;
        flex-direction: column;
        gap: 2px;
        margin-top: 4px;
    }

    .notion-calendario-event {
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 0.7rem;
        cursor: pointer;
        transition: all 0.15s ease;
        overflow: hidden;
    }

    .notion-calendario-event:hover {
        transform: scale(1.02);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .notion-calendario-event.completed {
        opacity: 0.5;
        text-decoration: line-through;
    }

    .notion-calendario-event.overdue {
        border-left: 3px solid #e03e3e;
    }

    .notion-calendario-event-text {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .notion-calendario-more {
        font-size: 0.65rem;
        color: #9b9a97;
        padding: 2px 6px;
    }

    .notion-calendario-chip {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .notion-calendario-chip:hover {
        transform: scale(1.02);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    @media (max-width: 768px) {
        .notion-calendario-day {
            min-height: 60px;
        }

        .notion-calendario-event-text {
            display: none;
        }

        .notion-calendario-event {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            padding: 0;
        }

        .notion-calendario-events {
            flex-direction: row;
            flex-wrap: wrap;
            gap: 4px;
        }
    }
</style>
