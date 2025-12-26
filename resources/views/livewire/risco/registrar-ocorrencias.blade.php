<div>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('riscos.index') }}" class="text-decoration-none">Riscos</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Ocorrências</li>
                    </ol>
                </nav>
                <h2 class="h4 fw-bold mb-0">Histórico de Materialização</h2>
                <p class="text-muted small mb-0">{{ $risco->dsc_titulo }}</p>
            </div>
            <button wire:click="create" class="btn btn-danger gradient-theme-btn">
                <i class="bi bi-exclamation-octagon me-2"></i>Registrar Nova
            </button>
        </div>
    </x-slot>

    @if (session()->has('status'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-10 mx-auto">
            @forelse($ocorrencias as $oc)
                <div class="card border-0 shadow-sm mb-4 overflow-hidden border-start border-4" style="border-color: {{ $oc->getImpactoRealCor() }} !important;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="badge bg-light text-dark border px-3 mb-2">{{ $oc->dte_ocorrencia?->format('d/m/Y') }}</span>
                                <h5 class="fw-bold text-dark mb-0">{{ $oc->txt_descricao }}</h5>
                            </div>
                            <div class="text-end">
                                <span class="badge rounded-pill px-3 py-2" style="background-color: {{ $oc->getImpactoRealCor() }}22; color: {{ $oc->getImpactoRealCor() }}; border: 1px solid {{ $oc->getImpactoRealCor() }}44;">
                                    Impacto: {{ $oc->getImpactoRealLabel() }}
                                </span>
                                <div class="mt-2">
                                    <button wire:click="edit('{{ $oc->cod_ocorrencia }}')" class="btn btn-sm btn-link text-muted"><i class="bi bi-pencil"></i></button>
                                    <button wire:click="delete('{{ $oc->cod_ocorrencia }}')" class="btn btn-sm btn-link text-danger" onclick="confirm('Excluir registro?')"><i class="bi bi-trash"></i></button>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mt-2">
                            <div class="col-md-6">
                                <label class="text-muted small text-uppercase fw-bold d-block mb-1">Ações Tomadas</label>
                                <p class="small bg-light p-3 rounded-3">{{ $oc->txt_acoes_tomadas ?: 'Nenhuma ação registrada.' }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small text-uppercase fw-bold d-block mb-1">Lições Aprendidas</label>
                                <p class="small bg-light p-3 rounded-3">{{ $oc->txt_licoes_aprendidas ?: 'Nenhuma lição registrada.' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="bi bi-calendar-x fs-1 opacity-25 mb-3 d-block"></i>
                    <h5 class="text-muted">Nenhuma ocorrência registrada para este risco.</h5>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Modal CRUD -->
    <div class="modal fade @if($showModal) show @endif" tabindex="-1" style="@if($showModal) display: block; background: rgba(0,0,0,0.5); @else display: none; @endif">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger text-white border-0">
                    <h5 class="modal-title fw-bold">Registrar Ocorrência do Risco</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showModal', false)"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body p-4">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small text-uppercase fw-bold">Data da Ocorrência</label>
                                <input type="date" wire:model="form.dte_ocorrencia" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small text-uppercase fw-bold">Impacto Real Observado</label>
                                <select wire:model="form.num_impacto_real" class="form-select">
                                    <option value="1">Muito Baixo</option>
                                    <option value="2">Baixo</option>
                                    <option value="3">Médio</option>
                                    <option value="4">Alto</option>
                                    <option value="5">Muito Alto</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small text-uppercase fw-bold">Descrição do Evento</label>
                            <textarea wire:model="form.txt_descricao" class="form-control" rows="2" placeholder="O que aconteceu?"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small text-uppercase fw-bold">Ações Tomadas Imediatamente</label>
                            <textarea wire:model="form.txt_acoes_tomadas" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-0">
                            <label class="form-label text-muted small text-uppercase fw-bold">Lições Aprendidas</label>
                            <textarea wire:model="form.txt_licoes_aprendidas" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light" wire:click="$set('showModal', false)">Cancelar</button>
                        <button type="submit" class="btn btn-danger px-4">Salvar Registro</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>