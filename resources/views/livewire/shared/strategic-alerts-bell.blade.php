@php
    $totalNovo = $unreadCount + $atividadeNova;
    $textoAtividade = $atividadeNova > \App\Support\Auditoria\FeedDeAtividade::TETO_CONTADOR ? '99+' : $atividadeNova;
    $rotuloSino = 'Notificações: '.$unreadCount.' '.($unreadCount === 1 ? 'alerta não lido' : 'alertas não lidos')
        .' e '.$textoAtividade.' '.($atividadeNova === 1 ? 'atividade nova' : 'atividades novas');
    $coresNivel = ['informativo' => 'info', 'atencao' => 'warning', 'critico' => 'danger'];
@endphp
{{--
    Poll de 1 minuto, só com o sino visível (o Livewire ainda reduz o ritmo com a
    aba do navegador em segundo plano). atualizarContadores() não re-renderiza:
    os contadores abaixo leem $wire pelo Alpine.
--}}
<div class="dropdown" wire:ignore.self wire:poll.60s.visible="atualizarContadores"
     x-data="{
        get alertas() { return Number($wire.unreadCount) || 0 },
        get atividade() { return Number($wire.atividadeNova) || 0 },
        get total() { return this.alertas + this.atividade },
        get textoAtividade() { return this.atividade > {{ \App\Support\Auditoria\FeedDeAtividade::TETO_CONTADOR }} ? '99+' : String(this.atividade) },
        get rotulo() {
            return 'Notificações: ' + this.alertas + (this.alertas === 1 ? ' alerta não lido' : ' alertas não lidos')
                + ' e ' + this.textoAtividade + (this.atividade === 1 ? ' atividade nova' : ' atividades novas');
        }
     }">
    <button class="btn btn-icon btn-ghost-secondary position-relative rounded-circle" type="button"
            data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"
            aria-label="{{ $rotuloSino }}" title="{{ $rotuloSino }}" :aria-label="rotulo" :title="rotulo" wire:ignore.self>
        <i class="bi bi-bell fs-5" aria-hidden="true"></i>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light"
              style="padding: 0.35em 0.5em; font-size: 0.6rem;{{ $totalNovo > 0 ? '' : ' display: none;' }}" aria-hidden="true"
              x-show="total > 0" x-text="total > 9 ? '9+' : total">{{ $totalNovo > 9 ? '9+' : $totalNovo }}</span>
    </button>
    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-0 overflow-hidden" wire:ignore.self
         style="width: min(360px, calc(100vw - 1.5rem)); border-radius: 16px;">

        <div class="d-flex border-bottom bg-body-tertiary" role="tablist" aria-label="Notificações">
            <button type="button" role="tab" id="sino-aba-alertas" aria-controls="sino-painel"
                    aria-selected="{{ $aba === 'alertas' ? 'true' : 'false' }}"
                    class="btn btn-sm flex-fill rounded-0 py-2 small fw-semibold border-0 {{ $aba === 'alertas' ? 'border-bottom border-2 border-primary text-primary' : 'text-body-secondary' }}"
                    wire:click="abrirAlertas">
                {{ __('Alertas') }}
                <span class="badge rounded-pill bg-danger ms-1" @if($unreadCount === 0) style="display: none;" @endif
                      x-show="alertas > 0" x-text="alertas > 9 ? '9+' : alertas">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
            </button>
            <button type="button" role="tab" id="sino-aba-atividade" aria-controls="sino-painel"
                    aria-selected="{{ $aba === 'atividade' ? 'true' : 'false' }}"
                    class="btn btn-sm flex-fill rounded-0 py-2 small fw-semibold border-0 {{ $aba === 'atividade' ? 'border-bottom border-2 border-primary text-primary' : 'text-body-secondary' }}"
                    wire:click="abrirAtividade">
                {{ __('Atividade') }}
                <span class="badge rounded-pill bg-primary ms-1" @if($atividadeNova === 0) style="display: none;" @endif
                      x-show="atividade > 0" x-text="textoAtividade">{{ $textoAtividade }}</span>
            </button>
        </div>

        <div id="sino-painel" role="tabpanel" aria-labelledby="sino-aba-{{ $aba }}" wire:loading.class="opacity-50" wire:target="abrirAlertas,abrirAtividade">
            @if($aba === 'alertas')
                <div class="px-3 py-2 d-flex justify-content-between align-items-center border-bottom">
                    <h6 class="fw-bold mb-0 text-body small text-uppercase">{{ __('Alertas Estratégicos') }}</h6>
                    @if($unreadCount > 0)
                        <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-primary small" wire:click="markAllAsRead">
                            {{ __('Marcar como lidas') }}
                        </button>
                    @endif
                </div>

                <div class="overflow-auto" style="max-height: 350px; background: var(--bs-body-bg);">
                    @forelse($alerts as $alert)
                        <div class="p-3 border-bottom hover-bg-light transition-all cursor-pointer @if(!$alert->read_at) bg-primary bg-opacity-10 @endif">
                            <div class="d-flex gap-3">
                                <div class="rounded-circle bg-{{ $alert->type }}-subtle text-{{ $alert->type }} d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 40px; height: 40px;">
                                    <i class="bi {{ $alert->icon }} fs-5" aria-hidden="true"></i>
                                </div>
                                <div class="flex-grow-1 min-width-0">
                                    <div class="fw-bold text-body small text-truncate">{{ $alert->title }}</div>
                                    <p class="small text-body-secondary mb-1 lh-sm" style="font-size: 0.75rem;">{{ strip_tags((string) $alert->message) }}</p>
                                    <div class="text-body-secondary opacity-75" style="font-size: 0.65rem;">
                                        <i class="bi bi-clock me-1" aria-hidden="true"></i>{{ $alert->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-5 text-center text-body-secondary">
                            <i class="bi bi-bell-slash fs-2 opacity-25 d-block mb-2" aria-hidden="true"></i>
                            <span class="small">Nenhum alerta recente.</span>
                        </div>
                    @endforelse
                </div>
            @else
                <div class="px-3 py-2 d-flex justify-content-between align-items-center border-bottom">
                    <h6 class="fw-bold mb-0 text-body small text-uppercase">{{ __('O que outras pessoas fizeram') }}</h6>
                    <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-primary small" wire:click="marcarAtividadeComoLida">
                        {{ __('Marcar como lido') }}
                    </button>
                </div>

                <ul class="list-unstyled mb-0 overflow-auto" style="max-height: 380px; background: var(--bs-body-bg);">
                    @forelse($atividades as $item)
                        @php($cor = $coresNivel[$item['nivel']])
                        <li wire:key="atividade-{{ $item['chave'] }}"
                            class="border-bottom border-start border-4 border-{{ $cor }} @if($item['novo']) bg-primary bg-opacity-10 @endif">
                            @if($item['link'])
                                <a href="{{ $item['link'] }}" class="d-flex gap-2 p-3 text-decoration-none text-body">
                            @else
                                <div class="d-flex gap-2 p-3">
                            @endif
                                <i class="bi {{ $item['icone'] }} text-{{ $cor }} fs-5 flex-shrink-0" aria-hidden="true"></i>
                                <div class="flex-grow-1" style="min-width: 0;">
                                    <p class="small mb-1 lh-sm text-break">
                                        <span class="badge bg-{{ $cor }}-subtle text-{{ $cor }}-emphasis border border-{{ $cor }}-subtle me-1">{{ $item['rotuloNivel'] }}</span>
                                        @if($item['novo'])<span class="visually-hidden">Novo. </span>@endif
                                        {{ $item['frase'] }}
                                    </p>
                                    <time class="text-body-secondary d-block" style="font-size: 0.7rem;"
                                          datetime="{{ $item['quando']->toIso8601String() }}"
                                          title="{{ $item['quando']->timezone(config('app.timezone'))->format('d/m/Y H:i') }}">
                                        <i class="bi bi-clock me-1" aria-hidden="true"></i>{{ $item['relativo'] }}
                                    </time>
                                </div>
                            @if($item['link'])
                                </a>
                            @else
                                </div>
                            @endif
                        </li>
                    @empty
                        <li class="p-5 text-center text-body-secondary">
                            <i class="bi bi-activity fs-2 opacity-25 d-block mb-2" aria-hidden="true"></i>
                            <span class="small">Nenhuma atividade de outras pessoas por aqui.</span>
                        </li>
                    @endforelse
                </ul>

                @if($linkAuditoria && count($atividades) >= \App\Support\Auditoria\FeedDeAtividade::LIMITE)
                    <div class="p-2 bg-body-tertiary text-center border-top">
                        <a href="{{ $linkAuditoria }}" class="btn btn-link btn-sm text-decoration-none small w-100">
                            {{ __('Ver mais na Auditoria') }}
                        </a>
                    </div>
                @elseif(count($atividades) >= \App\Support\Auditoria\FeedDeAtividade::LIMITE)
                    <div class="p-2 bg-body-tertiary text-center border-top small text-body-secondary">
                        {{ __('Mostrando as :n atividades mais recentes.', ['n' => \App\Support\Auditoria\FeedDeAtividade::LIMITE]) }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
