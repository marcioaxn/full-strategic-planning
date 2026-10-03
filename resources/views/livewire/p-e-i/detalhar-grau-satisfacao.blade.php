<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('graus-satisfacao.index') }}" wire:navigate class="text-decoration-none">Graus de Satisfação</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $grau->dsc_grau_satisfacao }}</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold text-gray-800 mb-0">
                <i class="bi bi-palette me-2" style="color: {{ $grau->cor }}"></i>Detalhes do Grau
            </h2>
            <p class="text-muted mb-0">
                {{ $grau->pei ? $grau->pei->dsc_pei : 'Padrão Global' }} 
                @if($grau->num_ano) • Ano {{ $grau->num_ano }} @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('graus-satisfacao.index') }}" wire:navigate class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Voltar
            </a>
            @if(auth()->user()?->can('modulo.editar', 'graus-satisfacao') && auth()->user()->can('editar-institucional'))
            <a href="{{ route('graus-satisfacao.index', ['editar' => $grau->cod_grau_satisfacao]) }}" class="btn btn-primary gradient-theme">
                <i class="bi bi-pencil me-1"></i> Editar
            </a>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <!-- Coluna Esquerda: Configuração Visual -->
        <div class="col-md-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="card-title mb-0 fw-bold">Configuração Visual</h6>
                </div>
                <div class="card-body text-center py-5">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle shadow-lg mb-4" 
                         style="width: 120px; height: 120px; background-color: {{ $grau->cor }}; color: {{ \App\Support\CorLegivel::textoSobre($grau->cor) }}; border: 4px solid rgba(255,255,255,0.5);">
                        <i class="bi bi-speedometer2 fs-1"></i>
                    </div>
                    
                    <h4 class="fw-bold mb-1">{{ $grau->dsc_grau_satisfacao }}</h4>
                    <p class="text-muted mb-4">@brazil_number($grau->vlr_minimo, 2)% a @brazil_number($grau->vlr_maximo, 2)%</p>

                    <div class="row g-2 justify-content-center">
                        <div class="col-auto">
                            <span class="badge bg-light text-dark border p-2">
                                <i class="bi bi-palette-fill me-1" style="color: {{ $grau->cor }}"></i> {{ $grau->cor }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Regra de Aplicação -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="card-title mb-0 fw-bold">Regra de Aplicação</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Escopo</span>
                            <span class="fw-bold">{{ $grau->pei ? 'Específico (PEI)' : 'Global' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Ano de Referência</span>
                            <span class="fw-bold">{{ $grau->num_ano ?? 'Todos' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Intervalo</span>
                            <span class="font-monospace">{{ number_format($grau->vlr_minimo, 1, ',', '.') }}% - {{ number_format($grau->vlr_maximo, 1, ',', '.') }}%</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Coluna Direita: Uso e Impacto -->
        <div class="col-md-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="card-title mb-0 fw-bold">Indicadores nesta Faixa</h6>
                </div>
                @if(count($indicadoresNaFaixa) > 0)
                    <div class="list-group list-group-flush">
                        @foreach($indicadoresNaFaixa as $ind)
                            <a href="{{ route('indicadores.detalhes', $ind['cod']) }}" wire:navigate class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold">{{ $ind['nome'] }}</div>
                                    @if($ind['objetivo'])<small class="text-muted">{{ $ind['objetivo'] }}</small>@endif
                                </div>
                                <span class="badge rounded-pill" style="background: {{ $grau->cor }};">@brazil_percent($ind['atingimento'], 1)</span>
                            </a>
                        @endforeach
                    </div>
                    <div class="card-footer bg-white small text-muted">Atingimento em {{ $ano }}, com lançamentos realizados.</div>
                @else
                    <div class="card-body text-center py-5">
                        <i class="bi bi-bar-chart-line fs-1 text-muted opacity-50 mb-3"></i>
                        <p class="small text-muted mb-0">
                            Nenhum indicador {{ $grau->pei ? 'deste ciclo' : '' }} tem atingimento nesta faixa em {{ $ano }}.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
