{{--
    Detalhe de um registro de auditoria, escrito para ser lido por quem não
    conhece o banco: frase-resumo no topo, campos com nome em português,
    chaves estrangeiras traduzidas em nomes, textos longos com a diferença
    marcada palavra a palavra, e os dados técnicos recolhidos no fim.
    A tradução é de App\Support\AuditoriaLegivel. Todo valor vem de dado
    auditado (texto do usuário): sempre {{ }}, nunca {!! !!}.
--}}
@php
    use App\Support\AuditoriaLegivel;
    use App\Support\RotuloAuditoria;

    $tipoRegistro = RotuloAuditoria::registro($log->auditable_type);
    $corEvento = ['created' => 'success', 'updated' => 'primary', 'deleted' => 'danger', 'restored' => 'info'][$log->event] ?? 'secondary';
    $iconeEvento = ['created' => 'plus-circle', 'updated' => 'pencil-square', 'deleted' => 'trash3', 'restored' => 'arrow-counterclockwise'][$log->event] ?? 'clock-history';
    $iniciais = $log->user
        ? collect(preg_split('/\s+/', trim($log->user->name)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->join('')
        : 'S';
    $tituloLista = match ($log->event) {
        'created' => 'Informações registradas na criação',
        'deleted' => 'Como o registro estava no momento da exclusão',
        'restored' => 'Informações restauradas',
        default => 'O que mudou',
    };
    $ladoUnico = in_array($log->event, ['created', 'deleted', 'restored'], true);
@endphp

<div class="container-fluid py-4 auditoria-detalhe">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('audit.index') }}" wire:navigate class="text-decoration-none">Auditoria</a></li>
                <li class="breadcrumb-item active" aria-current="page">Registro nº {{ $log->id }}</li>
            </ol>
        </nav>
        <a href="{{ route('audit.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Voltar à lista
        </a>
    </div>

    {{-- Frase-resumo: quem fez o quê, em quê e quando --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4 d-flex gap-3 align-items-start flex-wrap flex-md-nowrap">
            <div class="rounded-circle bg-{{ $corEvento }}-subtle text-{{ $corEvento }}-emphasis d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width: 56px; height: 56px; font-size: 1.5rem;" aria-hidden="true">
                <i class="bi bi-{{ $iconeEvento }}"></i>
            </div>
            <div class="flex-grow-1">
                <p class="h5 fw-normal mb-1 lh-base">
                    <strong>{{ $log->user->name ?? 'O sistema' }}</strong>
                    {{ AuditoriaLegivel::verbo($log->event) }}
                    {{ AuditoriaLegivel::comArtigo($tipoRegistro) }}
                    @if($nomeRegistro)
                        <strong>“{{ $nomeRegistro }}”</strong>
                    @endif
                </p>
                <p class="text-body-secondary mb-2">
                    <i class="bi bi-calendar3 me-1" aria-hidden="true"></i>
                    {{ $log->created_at->format('d/m/Y') }} às {{ $log->created_at->format('H:i:s') }}
                    <span class="mx-1">·</span>{{ $log->created_at->diffForHumans() }}
                    <span class="mx-1">·</span>
                    <span class="badge bg-{{ $corEvento }}-subtle text-{{ $corEvento }}-emphasis border border-{{ $corEvento }}-subtle">{{ RotuloAuditoria::evento($log->event) }}</span>
                    <span class="badge bg-body-tertiary text-body border">{{ $tipoRegistro }}</span>
                </p>
                @if($linkRegistro)
                    <a href="{{ $linkRegistro }}" wire:navigate class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Abrir {{ mb_strtolower($tipoRegistro) }}
                    </a>
                @elseif(! $registroExiste)
                    <span class="small text-body-secondary"><i class="bi bi-info-circle me-1" aria-hidden="true"></i>Este registro não existe mais ou foi excluído; o que está abaixo é o que a auditoria guardou.</span>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- O conteúdo: campo a campo --}}
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body py-3 d-flex justify-content-between align-items-center">
                    <h2 class="h6 fw-bold mb-0">{{ $tituloLista }}</h2>
                    <span class="small text-body-secondary">{{ count($mudancas) }} {{ count($mudancas) === 1 ? 'campo' : 'campos' }}</span>
                </div>

                @if($ladoUnico && count($mudancas) > 0)
                    {{-- Criação, exclusão, restauração: um lado só — grade compacta rótulo/valor --}}
                    <dl class="row g-0 mb-0 small">
                        @foreach($mudancas as $m)
                            @php $v = $log->event === 'deleted' ? $m['antes'] : $m['depois']; @endphp
                            <dt class="col-sm-4 fw-semibold px-4 py-2 border-top bg-body-tertiary" wire:key="r-{{ $m['coluna'] }}">{{ $m['rotulo'] }}</dt>
                            <dd class="col-sm-8 px-4 py-2 border-top mb-0 text-break {{ $v['vazio'] ? 'fst-italic text-body-secondary' : '' }}" style="white-space: pre-wrap;">@if($v['cor'])<span class="d-inline-block rounded-circle border me-1 align-middle" style="width: 12px; height: 12px; background: {{ $v['cor'] }};" aria-hidden="true"></span>@endif{{ $v['texto'] }}</dd>
                        @endforeach
                    </dl>
                @endif

                @forelse($ladoUnico ? [] : $mudancas as $m)
                    <div class="px-4 py-3 border-top" wire:key="campo-{{ $m['coluna'] }}">
                        <div class="fw-semibold mb-2">{{ $m['rotulo'] }}</div>

                        @if($m['trechos'])
                            {{-- Texto longo: a diferença palavra a palavra, e os dois textos inteiros sob demanda --}}
                            <div class="auditoria-diferenca p-3 rounded border bg-body-tertiary" style="white-space: pre-wrap; line-height: 1.7;">@foreach($m['trechos'] as [$tipo, $texto])@if($tipo === 'removido')<del class="auditoria-removido" title="Removido">{{ $texto }}</del>@elseif($tipo === 'incluido')<ins class="auditoria-incluido" title="Incluído">{{ $texto }}</ins>@else{{ $texto }}@endif @endforeach</div>
                            <div class="small text-body-secondary mt-2">
                                <del class="auditoria-removido">trecho removido</del> · <ins class="auditoria-incluido">trecho incluído</ins>
                            </div>
                            <details class="mt-2 small">
                                <summary class="text-primary" role="button">Ver o texto completo de antes e de depois</summary>
                                <div class="row g-2 mt-1">
                                    <div class="col-md-6">
                                        <div class="text-body-secondary mb-1">Antes</div>
                                        @include('livewire.audit.partials.valor', ['v' => $m['antes'], 'tom' => 'danger'])
                                    </div>
                                    <div class="col-md-6">
                                        <div class="text-body-secondary mb-1">Depois</div>
                                        @include('livewire.audit.partials.valor', ['v' => $m['depois'], 'tom' => 'success'])
                                    </div>
                                </div>
                            </details>
                        @else
                            <div class="row g-2 align-items-stretch">
                                <div class="col-md">
                                    <div class="small text-body-secondary mb-1">Antes</div>
                                    @include('livewire.audit.partials.valor', ['v' => $m['antes'], 'tom' => 'danger'])
                                </div>
                                <div class="col-md-auto d-none d-md-flex align-items-center pt-3 text-body-secondary" aria-hidden="true">
                                    <i class="bi bi-arrow-right fs-5"></i>
                                </div>
                                <div class="col-md">
                                    <div class="small text-body-secondary mb-1">Depois</div>
                                    @include('livewire.audit.partials.valor', ['v' => $m['depois'], 'tom' => 'success'])
                                </div>
                            </div>
                        @endif
                    </div>
                @empty
                    @if(! $ladoUnico || count($mudancas) === 0)
                        <div class="px-4 py-5 text-center text-body-secondary border-top">
                            Nenhum campo com valor foi registrado neste evento.
                        </div>
                    @endif
                @endforelse
            </div>
        </div>

        {{-- Contexto: quem, de onde, e a história do registro --}}
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-body py-3"><h2 class="h6 fw-bold mb-0">Quem fez e de onde</h2></div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3 flex-shrink-0"
                             style="width: 40px; height: 40px;" aria-hidden="true">{{ $iniciais }}</div>
                        <div class="text-break">
                            <div class="fw-semibold">{{ $log->user->name ?? 'Sistema / processo automático' }}</div>
                            @if($log->user)<div class="small text-body-secondary">{{ $log->user->email }}</div>@endif
                            @if($assumidoLog = \App\Support\RotuloAuditoria::assumido($log->tags))
                                <div class="small text-warning-emphasis">Gravado assumindo a identidade de {{ $assumidoLog }}</div>
                            @endif
                        </div>
                    </div>
                    <dl class="row small mb-0">
                        <dt class="col-5 fw-normal text-body-secondary">Origem do acesso</dt>
                        <dd class="col-7 text-break">{{ AuditoriaLegivel::origem($log->ip_address) }}</dd>
                        <dt class="col-5 fw-normal text-body-secondary">Navegador</dt>
                        <dd class="col-7 text-break">{{ AuditoriaLegivel::navegador($log->user_agent) }}</dd>
                        <dt class="col-5 fw-normal text-body-secondary">Quando</dt>
                        <dd class="col-7 mb-0">{{ $log->created_at->format('d/m/Y H:i:s') }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body py-3">
                    <h2 class="h6 fw-bold mb-0">Histórico deste registro</h2>
                </div>
                <ul class="list-group list-group-flush">
                    @foreach($historico as $h)
                        <li class="list-group-item small {{ $h->id === $log->id ? 'bg-primary-subtle' : '' }}">
                            @if($h->id === $log->id)
                                <span class="fw-semibold">{{ RotuloAuditoria::evento($h->event) }}</span> · este registro
                            @else
                                <a href="{{ route('audit.detalhes', $h->id) }}" wire:navigate class="fw-semibold text-decoration-none">{{ RotuloAuditoria::evento($h->event) }}</a>
                            @endif
                            <div class="text-body-secondary">{{ $h->created_at->format('d/m/Y H:i') }} · {{ $h->user->name ?? 'Sistema' }}</div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    {{-- Para o suporte técnico: tudo o que a tabela guarda, sem tradução --}}
    <details class="card border-0 shadow-sm mt-4">
        <summary class="card-header bg-body py-3 fw-semibold" role="button">
            <i class="bi bi-code-slash me-1" aria-hidden="true"></i> Detalhes técnicos (para o suporte)
        </summary>
        <div class="card-body small">
            <dl class="row mb-3">
                <dt class="col-md-3 fw-normal text-body-secondary">Nº do evento</dt><dd class="col-md-9 font-monospace">{{ $log->id }}</dd>
                <dt class="col-md-3 fw-normal text-body-secondary">Tipo técnico</dt><dd class="col-md-9 font-monospace text-break">{{ $log->auditable_type }}</dd>
                <dt class="col-md-3 fw-normal text-body-secondary">Identificador do registro</dt><dd class="col-md-9 font-monospace text-break">{{ $log->auditable_id }}</dd>
                <dt class="col-md-3 fw-normal text-body-secondary">Endereço IP</dt><dd class="col-md-9 font-monospace">{{ $log->ip_address ?: '—' }}</dd>
                <dt class="col-md-3 fw-normal text-body-secondary">Endereço acessado</dt><dd class="col-md-9 font-monospace text-break">{{ $log->url ?: '—' }}</dd>
                <dt class="col-md-3 fw-normal text-body-secondary">Navegador (completo)</dt><dd class="col-md-9 font-monospace text-break">{{ $log->user_agent ?: '—' }}</dd>
            </dl>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="text-body-secondary mb-1">Valores antigos (como gravados)</div>
                    <pre class="bg-body-tertiary border rounded p-2 mb-0 small" style="white-space: pre-wrap; word-break: break-all;">{{ AuditoriaLegivel::brutoMascarado($log->old_values) }}</pre>
                </div>
                <div class="col-md-6">
                    <div class="text-body-secondary mb-1">Valores novos (como gravados)</div>
                    <pre class="bg-body-tertiary border rounded p-2 mb-0 small" style="white-space: pre-wrap; word-break: break-all;">{{ AuditoriaLegivel::brutoMascarado($log->new_values) }}</pre>
                </div>
            </div>
        </div>
    </details>

    <style>
        .auditoria-detalhe .auditoria-removido { background: var(--bs-danger-bg-subtle); color: var(--bs-danger-text-emphasis); text-decoration: line-through; border-radius: 3px; padding: 0 2px; }
        .auditoria-detalhe .auditoria-incluido { background: var(--bs-success-bg-subtle); color: var(--bs-success-text-emphasis); text-decoration: none; border-radius: 3px; padding: 0 2px; font-weight: 600; }
        .auditoria-detalhe details > summary { list-style: none; }
        .auditoria-detalhe details > summary::-webkit-details-marker { display: none; }
        .auditoria-detalhe details > summary::before { content: '▸ '; }
        .auditoria-detalhe details[open] > summary::before { content: '▾ '; }
    </style>
</div>
