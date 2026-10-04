{{--
    /entregas sem iniciativa na URL. Entrega só existe dentro de uma iniciativa
    (regra do BSC): em vez de abrir o quadro de uma iniciativa escolhida pelo
    sistema, a pessoa escolhe onde vai trabalhar.
--}}
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb" class="mb-2">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" wire:navigate class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('planos.index') }}" wire:navigate class="text-decoration-none">Iniciativas</a></li>
            <li class="breadcrumb-item active" aria-current="page">Entregas</li>
        </ol>
    </nav>

    <h2 class="h4 fw-bold mb-1"><i class="bi bi-list-check me-2 text-primary" aria-hidden="true"></i>Entregas — escolha a iniciativa</h2>
    <p class="text-body-secondary mb-4">
        Toda entrega pertence a uma iniciativa. Escolha abaixo em qual iniciativa você vai ver ou cadastrar entregas.
    </p>

    <x-secao-educativa
        titulo="O que são Entregas?"
        subtitulo="Os produtos concretos que uma iniciativa precisa produzir."
        icone="list-check"
        por-que="Iniciativa sem entregas definidas não tem como ser acompanhada: não se sabe o que falta, quem faz e até quando. A entrega é o que transforma a iniciativa em trabalho verificável."
        :passos="[
            'Escolha abaixo a iniciativa em que você vai trabalhar.',
            'No quadro que se abre, use o botão de nova entrega.',
            'Descreva o produto (o que estará pronto), o responsável e o prazo.',
            'Atualize a situação da entrega à medida que o trabalho anda.',
        ]"
        exemplo="Na iniciativa “Digitalizar o atendimento ao produtor rural” (fictícia), uma entrega é “Formulário de cadastro disponível no portal”, com responsável e prazo definidos."
        dica="descreva a entrega como um resultado pronto (“manual publicado”), não como uma atividade (“elaborar manual”)."
        referencia="GPPEI p. 32, 43 e 147–150">
        Se a iniciativa é a viagem, as entregas são as paradas do caminho: cada uma tem um ponto de chegada claro.
        Uma <strong>entrega</strong> é um produto concreto de uma <strong>iniciativa</strong> — por isso não existe entrega solta,
        fora de uma iniciativa.
    </x-secao-educativa>

    @if($iniciativasPorObjetivo->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body p-5 text-center">
                <i class="bi bi-inbox fs-1 text-body-secondary d-block mb-3" aria-hidden="true"></i>
                <h3 class="h5">Nenhuma iniciativa neste ciclo e nesta unidade</h3>
                <p class="text-body-secondary mb-4">
                    As entregas são cadastradas dentro de uma iniciativa. Confira a unidade e o ciclo selecionados no topo, ou cadastre a iniciativa primeiro.
                </p>
                <a href="{{ route('planos.index') }}" class="btn btn-primary" wire:navigate>
                    <i class="bi bi-arrow-right me-1" aria-hidden="true"></i>Ir para Iniciativas
                </a>
            </div>
        </div>
    @else
        @foreach($iniciativasPorObjetivo as $objetivo => $iniciativas)
            <div class="card border-0 shadow-sm mb-3" wire:key="obj-{{ md5($objetivo) }}">
                <div class="card-header bg-body py-3">
                    <span class="small text-body-secondary d-block">Objetivo estratégico</span>
                    <span class="fw-semibold">{{ $objetivo }}</span>
                </div>
                <div class="list-group list-group-flush">
                    @foreach($iniciativas as $iniciativa)
                        <a href="{{ route('planos.entregas', $iniciativa->cod_plano_de_acao) }}" wire:navigate
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center gap-3 py-3"
                           wire:key="ini-{{ $iniciativa->cod_plano_de_acao }}">
                            <span>
                                <span class="fw-semibold d-block">{{ $iniciativa->dsc_plano_de_acao }}</span>
                                <span class="small text-body-secondary">{{ $iniciativa->organizacao?->sgl_organizacao }} · {{ $iniciativa->bln_status }}</span>
                            </span>
                            <span class="d-flex align-items-center gap-2 flex-shrink-0">
                                <span class="badge bg-body-tertiary text-body border">{{ $iniciativa->entregas_count }} {{ $iniciativa->entregas_count === 1 ? 'entrega' : 'entregas' }}</span>
                                <i class="bi bi-chevron-right text-body-secondary" aria-hidden="true"></i>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif
</div>
