<div>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('riscos.index') }}" class="text-decoration-none">Riscos</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Matriz de Exposição</li>
                    </ol>
                </nav>
                <h2 class="h4 fw-bold mb-0">Mapa de Calor de Riscos (Heatmap)</h2>
            </div>
        </div>
    </x-slot>

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom text-center">
            <h5 class="mb-0 fw-bold text-uppercase">Matriz Probabilidade x Impacto - {{ $organizacaoNome }}</h5>
        </div>
        <div class="card-body p-5">
            <div class="d-flex">
                <!-- Eixo Y: Impacto -->
                <div class="d-flex flex-column justify-content-around pe-3 text-center fw-bold text-muted small" style="width: 100px;">
                    <div style="transform: rotate(-90deg); white-space: nowrap; margin-bottom: 20px;">IMPACTO</div>
                    <div>Muito Alto (5)</div>
                    <div>Alto (4)</div>
                    <div>Médio (3)</div>
                    <div>Baixo (2)</div>
                    <div>Muito Baixo (1)</div>
                </div>

                <!-- O Grid -->
                <div class="flex-grow-1">
                    <div class="risk-grid">
                        @for($i=5; $i>=1; $i--)
                            <div class="risk-row d-flex">
                                @for($j=1; $j<=5; $j++)
                                    @php
                                        $nivel = $i * $j;
                                        $bgColor = '#65a30d'; // Verde (Baixo)
                                        if ($nivel >= 16) $bgColor = '#dc2626'; // Vermelho (Crítico)
                                        elseif ($nivel >= 10) $bgColor = '#f97316'; // Laranja (Alto)
                                        elseif ($nivel >= 5) $bgColor = '#eab308'; // Amarelo (Médio)
                                        
                                        $riscosNaCelula = $matriz[$i][$j] ?? [];
                                    @endphp
                                    <div class="risk-cell border d-flex flex-wrap align-items-center justify-content-center p-2" 
                                         style="background-color: {{ $bgColor }}22; min-height: 100px; flex: 1; border-color: {{ $bgColor }}44 !important;">
                                        @foreach($riscosNaCelula as $r)
                                            <a href="{{ route('riscos.index') }}?search={{ $r->dsc_titulo }}" 
                                               class="badge rounded-pill bg-white text-dark border shadow-sm m-1 text-decoration-none py-1 px-2 animate-pop" 
                                               title="{{ $r->dsc_titulo }}"
                                               style="font-size: 0.65rem; border-color: {{ $bgColor }} !important;">
                                                R-{{ str_pad($r->num_codigo_risco, 2, '0', STR_PAD_LEFT) }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endfor
                            </div>
                        @endfor
                    </div>
                    
                    <!-- Eixo X: Probabilidade -->
                    <div class="d-flex justify-content-around pt-3 fw-bold text-muted small ms-n1">
                        <div style="flex: 1; text-align: center;">Muito Baixa (1)</div>
                        <div style="flex: 1; text-align: center;">Baixa (2)</div>
                        <div style="flex: 1; text-align: center;">Média (3)</div>
                        <div style="flex: 1; text-align: center;">Alta (4)</div>
                        <div style="flex: 1; text-align: center;">Muito Alta (5)</div>
                    </div>
                    <div class="text-center mt-3 fw-bold text-muted small text-uppercase">PROBABILIDADE</div>
                </div>
            </div>
        </div>
        <div class="card-footer bg-light border-0 py-3 text-center">
            <div class="d-inline-flex gap-4">
                <small><span class="badge bg-success rounded-circle me-1">&nbsp;</span> Baixo</small>
                <small><span class="badge bg-warning rounded-circle me-1">&nbsp;</span> Médio</small>
                <small><span class="badge bg-orange rounded-circle me-1" style="background-color: #f97316;">&nbsp;</span> Alto</small>
                <small><span class="badge bg-danger rounded-circle me-1">&nbsp;</span> Crítico</small>
            </div>
        </div>
    </div>

    <style>
        .risk-grid { border: 2px solid #eee; }
        .risk-cell { transition: all 0.2s ease; }
        .risk-cell:hover { background-color: rgba(0,0,0,0.02) !important; z-index: 1; box-shadow: inset 0 0 10px rgba(0,0,0,0.05); }
        .animate-pop { animation: pop 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        @keyframes pop { from { opacity: 0; transform: scale(0.5); } to { opacity: 1; transform: scale(1); } }
    </style>
</div>