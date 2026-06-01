<div>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" wire:navigate class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('planos.index') }}" wire:navigate class="text-decoration-none">Planos de Ação</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('planos.detalhes', $plano->cod_plano_de_acao) }}" wire:navigate class="text-decoration-none">{{ Str::limit($plano->dsc_plano_de_acao, 30) }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Responsáveis</li>
                    </ol>
                </nav>
                <h2 class="h3 fw-bold mb-0 text-gray-800">
                    <i class="bi bi-people-fill me-2 text-primary"></i>Gestores e Responsáveis
                </h2>
                <div class="d-flex align-items-center gap-2 mt-1">
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 px-2">PLANO</span>
                    <span class="text-secondary fw-medium">{{ $plano->dsc_plano_de_acao }}</span>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('planos.detalhes', $plano->cod_plano_de_acao) }}" wire:navigate class="btn btn-outline-secondary shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Voltar para o Plano
                </a>
            </div>
        </div>
    </x-slot>

    @if (session()->has('status'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Coluna da Esquerda: Adicionar Novo -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-person-plus me-2 text-primary"></i>Nova Atribuição</h5>
                </div>
                <div class="card-body p-4 pt-0">
                    <form wire:submit.prevent="adicionar">
                        <div class="mb-3">
                            <label class="form-label text-muted small text-uppercase fw-bold">Selecionar Usuário</label>
                            <select wire:model="novo_usuario_id" class="form-select @error('novo_usuario_id') is-invalid @enderror">
                                <option value="">Escolha um usuário...</option>
                                @foreach($usuariosDisponiveis as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                            @error('novo_usuario_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted small text-uppercase fw-bold">Perfil de Gestão</label>
                            @foreach($perfisGestao as $perfil)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" 
                                           wire:model="novo_perfil_id" 
                                           value="{{ $perfil['id'] }}" 
                                           id="perfil_{{ $perfil['id'] }}">
                                    <label class="form-check-label" for="perfil_{{ $perfil['id'] }}">
                                        {{ $perfil['label'] }}
                                    </label>
                                </div>
                            @endforeach
                            @error('novo_perfil_id') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <button type="submit" class="btn btn-primary gradient-theme-btn w-100 py-2 fw-bold">
                            <i class="bi bi-plus-lg me-2"></i>Atribuir ao Plano
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Coluna da Direita: Lista Atual -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm overflow-hidden h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-people me-2 text-primary"></i>Responsáveis Atuais</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Nome / E-mail</th>
                                    <th>Perfil</th>
                                    <th class="text-end pe-4">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($responsaveis as $resp)
                                    <tr>
                                        <td class="ps-4 py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-3 bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold">
                                                    {{ substr($resp->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <span class="fw-bold d-block">{{ $resp->name }}</span>
                                                    <small class="text-muted">{{ $resp->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $resp->cod_perfil === \App\Models\PerfilAcesso::GESTOR_RESPONSAVEL ? 'bg-primary' : 'bg-info' }} bg-opacity-10 text-{{ $resp->cod_perfil === \App\Models\PerfilAcesso::GESTOR_RESPONSAVEL ? 'primary' : 'info' }} border border-{{ $resp->cod_perfil === \App\Models\PerfilAcesso::GESTOR_RESPONSAVEL ? 'primary' : 'info' }} rounded-pill px-3">
                                                {{ $resp->dsc_perfil }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <button wire:click="remover('{{ $resp->id }}')" 
                                                    class="btn btn-sm btn-outline-danger border-0"
                                                    onclick="return confirm('Remover atribuição?')">
                                                <i class="bi bi-person-x fs-5"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-5 text-muted">
                                            <i class="bi bi-person-exclamation fs-1 opacity-25 mb-3 d-block"></i>
                                            Nenhum responsável atribuído a este plano.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Matriz RACI --}}
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-3 px-4">
            <div>
                <h5 class="fw-bold mb-0"><i class="bi bi-people me-2 text-warning"></i>Matriz RACI</h5>
                <div class="mt-1 d-flex gap-2">
                    <x-gppei-link :page="120" label="RACI GPPEI" />
                    <x-projetos-link :page="89" label="Matriz RACI" />
                </div>
            </div>
            <button wire:click="novoRaci" class="btn btn-outline-warning rounded-pill px-3">
                <i class="bi bi-plus-lg me-1"></i>Atribuir Papel
            </button>
        </div>
        <div class="card-body p-3">
            @php
                $papeisMeta = [
                    'R' => ['label' => 'Responsável', 'color' => 'danger', 'desc' => 'executa'],
                    'A' => ['label' => 'Aprovador', 'color' => 'warning', 'desc' => 'accountability'],
                    'C' => ['label' => 'Consultado', 'color' => 'info', 'desc' => 'contribui'],
                    'I' => ['label' => 'Informado', 'color' => 'secondary', 'desc' => 'recebe resultado'],
                ];
            @endphp
            @if($racis->isEmpty())
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-people fs-1 opacity-25 d-block mb-2"></i>
                    <p class="small mb-0">Nenhum papel RACI atribuído. Defina quem é Responsável, Aprovador, Consultado e Informado.</p>
                </div>
            @else
                <div class="row g-3">
                    @foreach($papeisMeta as $papel => $meta)
                    @if($racis->has($papel))
                    <div class="col-md-6 col-lg-3">
                        <div class="card border-{{ $meta['color'] }} border-opacity-25 h-100">
                            <div class="card-header bg-{{ $meta['color'] }}-subtle py-2 px-3 border-0">
                                <span class="fw-bold small text-{{ $meta['color'] }}">{{ $papel }} — {{ $meta['label'] }}</span>
                                <small class="text-muted d-block" style="font-size:.65rem;">{{ $meta['desc'] }}</small>
                            </div>
                            <div class="card-body p-2">
                                @foreach($racis[$papel] as $raci)
                                <div class="d-flex align-items-center gap-2 py-1">
                                    <i class="bi bi-person-circle text-{{ $meta['color'] }} flex-shrink-0"></i>
                                    <div class="flex-grow-1" style="min-width:0;">
                                        <span class="small d-block text-truncate">{{ $raci->usuario?->name ?? '—' }}</span>
                                        @if($raci->entrega)
                                            <small class="text-muted d-block text-truncate" style="font-size:.65rem;">{{ Str::limit($raci->entrega->dsc_entrega, 30) }}</small>
                                        @else
                                            <small class="text-muted" style="font-size:.65rem;">Plano inteiro</small>
                                        @endif
                                    </div>
                                    <div class="d-flex gap-1 flex-shrink-0">
                                        <button wire:click="editarRaci('{{ $raci->cod_raci }}')" class="btn btn-xs btn-link p-0 text-muted"><i class="bi bi-pencil" style="font-size:.65rem;"></i></button>
                                        <button wire:click="excluirRaci('{{ $raci->cod_raci }}')" class="btn btn-xs btn-link p-0 text-danger"><i class="bi bi-x" style="font-size:.75rem;"></i></button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Modal: RACI --}}
    @if($showModalRaci)
    <div class="modal fade show" tabindex="-1" style="display:block;background:rgba(0,0,0,.5);z-index:1055;" wire:click.self="$set('showModalRaci',false)">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header gradient-theme-header text-white border-0 py-3 px-4">
                    <h5 class="modal-title fw-bold"><i class="bi bi-people me-2"></i>{{ $raciEditId ? 'Editar' : 'Atribuir' }} Papel RACI</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showModalRaci',false)"></button>
                </div>
                <form wire:submit.prevent="salvarRaci">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Usuário <span class="text-danger">*</span></label>
                            <select wire:model="formRaci.user_id" class="form-select @error('formRaci.user_id') is-invalid @enderror">
                                <option value="">Selecione...</option>
                                @foreach($usuariosDisponiveis as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                            @error('formRaci.user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Papel <span class="text-danger">*</span></label>
                            <select wire:model="formRaci.dsc_papel" class="form-select">
                                @foreach($papeisRaci as $cod => $desc)
                                    <option value="{{ $cod }}">{{ $cod }} — {{ $desc }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-bold small text-uppercase text-muted">Entrega (opcional)</label>
                            <select wire:model="formRaci.cod_entrega" class="form-select">
                                <option value="">Plano inteiro</option>
                                @foreach($entregasPlano as $ent)
                                    <option value="{{ $ent->cod_entrega }}">{{ Str::limit($ent->dsc_entrega, 50) }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Deixe em "Plano inteiro" para um papel geral do plano.</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" wire:click="$set('showModalRaci',false)">Cancelar</button>
                        <button type="submit" class="btn btn-primary gradient-theme-btn px-5 rounded-pill"><i class="bi bi-check-lg me-2"></i>Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Plano de Comunicação --}}
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-3 px-4">
            <div>
                <h5 class="fw-bold mb-0"><i class="bi bi-megaphone me-2 text-info"></i>Plano de Comunicação</h5>
                <div class="mt-1"><x-projetos-link :page="143" label="Domínio 5 — Partes Interessadas e Comunicação" /></div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('relatorios.comunicacao') }}" target="_blank" class="btn btn-outline-danger rounded-pill px-3" data-bs-toggle="tooltip" title="Relatório consolidado de comunicação do PEI em PDF">
                    <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                </a>
                <button wire:click="novaComunicacao" class="btn btn-outline-info rounded-pill px-3">
                    <i class="bi bi-plus-lg me-1"></i>Adicionar
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            @if($comunicacoes->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-megaphone fs-1 opacity-25 d-block mb-2"></i>
                    <p class="small mb-0">Nenhum item de comunicação definido para este plano.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light text-muted text-uppercase">
                            <tr>
                                <th class="ps-4">Público-Alvo</th>
                                <th>Mensagem-Chave</th>
                                <th>Canal</th>
                                <th>Frequência</th>
                                <th>Responsável</th>
                                <th class="text-end pe-4">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($comunicacoes as $com)
                            <tr>
                                <td class="ps-4 fw-semibold">{{ $com->nom_publico_alvo }}</td>
                                <td class="text-muted">{{ Str::limit($com->dsc_mensagem_chave, 50) }}</td>
                                <td><span class="badge bg-info-subtle text-info">{{ $com->dsc_canal }}</span></td>
                                <td>{{ $com->dsc_frequencia }}</td>
                                <td>{{ $com->nom_responsavel ?? '—' }}</td>
                                <td class="text-end pe-4">
                                    <button wire:click="editarComunicacao('{{ $com->cod_comunicacao }}')" class="btn btn-xs btn-outline-primary me-1 py-1 px-2"><i class="bi bi-pencil"></i></button>
                                    <button wire:click="excluirComunicacao('{{ $com->cod_comunicacao }}')" class="btn btn-xs btn-outline-danger py-1 px-2"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal: Plano de Comunicação --}}
    @if($showModalComun)
    <div class="modal fade show" tabindex="-1" style="display:block;background:rgba(0,0,0,.5);z-index:1055;" wire:click.self="$set('showModalComun',false)">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header gradient-theme-header text-white border-0 py-3 px-4">
                    <h5 class="modal-title fw-bold"><i class="bi bi-megaphone me-2"></i>{{ $comunEditId ? 'Editar' : 'Novo' }} Item de Comunicação</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showModalComun',false)"></button>
                </div>
                <form wire:submit.prevent="salvarComunicacao">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted">Público-Alvo <span class="text-danger">*</span></label>
                                <input type="text" wire:model="formComun.nom_publico_alvo" class="form-control @error('formComun.nom_publico_alvo') is-invalid @enderror" placeholder="Ex.: Alta Direção, Equipe técnica, Cidadãos...">
                                @error('formComun.nom_publico_alvo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted">Responsável pela Comunicação</label>
                                <input type="text" wire:model="formComun.nom_responsavel" class="form-control" placeholder="Nome do responsável...">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase text-muted">Mensagem-Chave <span class="text-danger">*</span></label>
                                <textarea wire:model="formComun.dsc_mensagem_chave" class="form-control @error('formComun.dsc_mensagem_chave') is-invalid @enderror" rows="2" placeholder="O que comunicar? Qual o objetivo desta comunicação?"></textarea>
                                @error('formComun.dsc_mensagem_chave') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted">Canal</label>
                                <select wire:model="formComun.dsc_canal" class="form-select">
                                    @foreach($canais as $c) <option value="{{ $c }}">{{ $c }}</option> @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted">Frequência</label>
                                <select wire:model="formComun.dsc_frequencia" class="form-select">
                                    @foreach($frequencias as $f) <option value="{{ $f }}">{{ $f }}</option> @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" wire:click="$set('showModalComun',false)">Cancelar</button>
                        <button type="submit" class="btn btn-primary gradient-theme-btn px-5 rounded-pill"><i class="bi bi-check-lg me-2"></i>Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>