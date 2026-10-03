<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" wire:navigate class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('riscos.index') }}" wire:navigate class="text-decoration-none">Gestão de Riscos</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Matriz de Riscos</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0">
                <i class="bi bi-grid-3x3-gap me-2 text-danger"></i>Matriz Visual de Riscos (5×5)
            </h2>
        </div>
        <a href="{{ route('riscos.index') }}" wire:navigate class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Voltar aos Riscos
        </a>
    </div>

    <x-secao-educativa
        titulo="O que é a Matriz de Riscos?"
        subtitulo="Um quadro que mostra quais problemas podem acontecer e quais são os mais perigosos."
        icone="grid-3x3-gap"
        por-que="A matriz ajuda a se antecipar às ameaças e a escolher onde agir primeiro, deixando a estratégia menos vulnerável."
        :passos="[
            'Cada risco aparece na casa que cruza a sua probabilidade (eixo de baixo) com o seu impacto (eixo do lado).',
            'Quanto mais para cima e para a direita, maior o nível de risco: as casas vermelhas são críticas.',
            'Comece pelos riscos das casas vermelhas e laranjas: eles precisam de plano de resposta.',
            'Para cadastrar ou mudar a nota de um risco, volte à tela Gestão de Riscos.',
        ]"
        exemplo="O Instituto Federal de Meteorologia Aplicada (fictício) tem o risco “Atraso na compra de radares por licitação deserta”: probabilidade média e impacto alto. Ele cai numa casa laranja e ganha prioridade de resposta."
        dica="a matriz não é feita uma vez só. Atualize as notas ao longo do ciclo e ajuste as respostas."
        referencia="GPPEI p. 93–96 · ISO 31000:2018 · TCU, Referencial Básico de Gestão de Riscos (2018)">
        Antes de um passeio, você pensa: pode chover? Se chover, estraga tudo? <strong>Risco</strong> é um evento que
        <em>pode</em> acontecer e atrapalhar um objetivo. A matriz cruza a <strong>probabilidade</strong> (a chance de acontecer)
        com o <strong>impacto</strong> (o tamanho do estrago). O resultado é o <strong>nível de risco</strong>, do baixo ao crítico.
    </x-secao-educativa>

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom text-center">
            <h5 class="mb-0 fw-bold text-uppercase">Matriz Probabilidade x Impacto - {{ $organizacaoNome }}</h5>
        </div>
        <div class="card-body p-5">
            <div class="d-flex">
                <!-- Eixo Y: Impacto. O rótulo de cada nível fica DENTRO da linha da
                     grade: numa coluna à parte, seis itens (título + 5 níveis)
                     se distribuíam na altura de cinco linhas e cada rótulo caía
                     ao lado da linha errada. -->
                <div class="d-flex align-items-center pe-2 fw-bold text-muted small">
                    <div style="writing-mode: vertical-rl; transform: rotate(180deg); white-space: nowrap;">IMPACTO</div>
                </div>

                <!-- O Grid -->
                <div class="flex-grow-1">
                    @php $rotulosImpacto = [5 => 'Muito Alto (5)', 4 => 'Alto (4)', 3 => 'Médio (3)', 2 => 'Baixo (2)', 1 => 'Muito Baixo (1)']; @endphp
                    <div class="risk-grid">
                        @for($i=5; $i>=1; $i--)
                            <div class="risk-row d-flex">
                                <div class="d-flex align-items-center justify-content-end pe-3 text-end fw-bold text-muted small" style="width: 100px; flex: 0 0 100px;">
                                    {{ $rotulosImpacto[$i] }}
                                </div>
                                @for($j=1; $j<=5; $j++)
                                    @php
                                        $nivel = $i * $j;
                                        $bgColor = '#65a30d'; // Verde (Baixo)
                                        if ($nivel >= 16) $bgColor = '#dc2626'; // Vermelho (Crítico)
                                        elseif ($nivel >= 10) $bgColor = '#f97316'; // Laranja (Alto)
                                        elseif ($nivel >= 5) $bgColor = '#eab308'; // Amarelo (Médio)
                                        
                                        $riscosNaCelula = $matriz[$i][$j] ?? [];
                                    @endphp
                                    <div class="risk-cell border d-flex flex-wrap align-items-start justify-content-start p-2" 
                                         style="background-color: {{ $bgColor }}15; min-height: 110px; flex: 1; border-color: {{ $bgColor }}33 !important;">
                                        @foreach($riscosNaCelula as $r)
                                            <a href="{{ route('riscos.index') }}?search={{ urlencode($r->dsc_titulo) }}" 
                                               class="risk-matrix-item animate-pop" 
                                               data-bs-toggle="tooltip"
                                               data-bs-placement="top"
                                               title="{{ $r->dsc_titulo }}">
                                                <div class="risk-color-bar" style="background-color: {{ $bgColor }};"></div>
                                                <span class="risk-title-text">{{ $r->dsc_titulo }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                @endfor
                            </div>
                        @endfor
                    </div>
                    
                    <!-- Eixo X: Probabilidade -->
                    <div class="d-flex justify-content-around pt-3 fw-bold text-muted small ms-n1">
                        <div style="flex: 0 0 100px;"></div>
                        <div style="flex: 1; text-align: center;">Muito Baixa (1)</div>
                        <div style="flex: 1; text-align: center;">Baixa (2)</div>
                        <div style="flex: 1; text-align: center;">Média (3)</div>
                        <div style="flex: 1; text-align: center;">Alta (4)</div>
                        <div style="flex: 1; text-align: center;">Muito Alta (5)</div>
                    </div>
                    <div class="text-center mt-3 fw-bold text-muted small text-uppercase" style="padding-left: 100px;">PROBABILIDADE</div>
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