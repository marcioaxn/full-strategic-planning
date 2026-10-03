<div>
    {{-- Cabeçalho --}}
    <div class="leads-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="icon-circle-header gradient-theme-icon">
                    <i class="bi bi-folder2-open"></i>
                </div>
                <h1 class="h3 fw-bold mb-0">Documentos</h1>
                <span class="badge-modern badge-count">{{ $documentos->total() }}</span>
            </div>
            <p class="text-muted mb-0">Acervo em PDF de decretos, portarias, relatórios de gestão e demais documentos do planejamento.</p>
        </div>

        @if($podeEnviar)
            <x-action-button variant="primary" icon="cloud-arrow-up" wire:click="create" class="btn-action-primary gradient-theme-btn">
                Enviar documento
            </x-action-button>
        @endif
    </div>

    @if (session()->has('status'))
        <div class="alert alert-modern alert-success alert-dismissible fade show d-flex align-items-center gap-3 mb-4" role="alert">
            <div class="alert-icon"><i class="bi bi-check-circle-fill"></i></div>
            <span class="flex-grow-1">{{ session('status') }}</span>
            <button type="button" class="btn-close btn-close-modern" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    {{-- Filtros --}}
    <div class="card card-modern filters-card mb-4">
        <div class="card-body p-4">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-lg-3">
                    <label for="doc-busca" class="form-label-modern"><i class="bi bi-search me-2"></i>Buscar</label>
                    <input id="doc-busca" type="search" class="form-control" placeholder="Nome, número, origem ou descrição" wire:model.live.debounce.300ms="search">
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <label for="doc-tipo" class="form-label-modern"><i class="bi bi-tag me-2"></i>Tipo</label>
                    <select id="doc-tipo" class="form-select form-select-modern" wire:model.live="filtroTipo">
                        <option value="">Todos</option>
                        @foreach(\App\Models\Documento::TIPOS as $grupo => $tipos)
                            <optgroup label="{{ $grupo }}">
                                @foreach($tipos as $tipo)
                                    <option value="{{ $tipo }}">{{ $tipo }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4 col-lg-2">
                    <label for="doc-pei" class="form-label-modern"><i class="bi bi-calendar-range me-2"></i>PEI</label>
                    <select id="doc-pei" class="form-select form-select-modern" wire:model.live="filtroPei">
                        <option value="">Todos</option>
                        @foreach($peis as $pei)
                            <option value="{{ $pei->cod_pei }}">{{ $pei->dsc_pei }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2 col-lg-2">
                    <label for="doc-ano" class="form-label-modern">Ano</label>
                    <select id="doc-ano" class="form-select form-select-modern" wire:model.live="filtroAno">
                        <option value="">Todos</option>
                        @foreach($anosComDocumento as $ano)
                            <option value="{{ $ano }}">{{ $ano }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2 col-lg-2">
                    @if($search !== '' || $filtroTipo !== '' || $filtroPei !== '' || $filtroAno !== '')
                        <button type="button" class="btn btn-outline-secondary btn-modern w-100" wire:click="limparFiltros">
                            <i class="bi bi-x-lg me-1"></i>Limpar
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Lista --}}
    <div class="card card-modern table-card">
        <div class="table-responsive">
            <table class="table table-modern mb-0">
                <thead>
                    <tr>
                        <th scope="col" class="ps-4">Documento</th>
                        <th scope="col">Tipo</th>
                        <th scope="col">Ano</th>
                        <th scope="col">Origem</th>
                        <th scope="col">PEI / Unidade</th>
                        <th scope="col" class="text-end pe-4">Ações</th>
                    </tr>
                </thead>
                <tbody wire:loading.class="loading-opacity" wire:target="search,filtroTipo,filtroPei,filtroAno,limparFiltros">
                    @forelse($documentos as $doc)
                        <tr class="table-row-hover" wire:key="doc-{{ $doc->cod_documento }}">
                            <td class="ps-4">
                                <div class="d-flex align-items-start gap-3">
                                    <i class="bi bi-file-earmark-pdf-fill text-danger fs-3"></i>
                                    <div>
                                        <a href="{{ route('acervo.arquivo', $doc->cod_documento) }}" target="_blank" rel="noopener" class="fw-semibold text-body-emphasis text-decoration-none hover-primary">{{ $doc->nom_documento }}</a>
                                        <small class="d-block text-muted">
                                            @if($doc->num_documento)Nº {{ $doc->num_documento }} · @endif
                                            @if($doc->dte_documento){{ $doc->dte_documento->format('d/m/Y') }} · @endif
                                            {{ $doc->tamanhoLegivel() }}
                                        </small>
                                        @if($doc->txt_descricao)
                                            <small class="d-block text-muted text-truncate" style="max-width: 420px;" title="{{ $doc->txt_descricao }}">{{ $doc->txt_descricao }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge-modern badge-secondary">{{ $doc->dsc_tipo }}</span></td>
                            <td>{{ $doc->num_ano_referencia ?? '—' }}</td>
                            <td>{{ $doc->dsc_origem ?? '—' }}</td>
                            <td>
                                <small class="d-block">{{ $doc->pei?->dsc_pei ?? 'Sem PEI' }}</small>
                                <small class="d-block text-muted">{{ $doc->organizacao ? ($doc->organizacao->sgl_organizacao ?: $doc->organizacao->nom_organizacao) : 'Institucional' }}</small>
                            </td>
                            <td class="text-end pe-4">
                                <div class="action-buttons d-inline-flex flex-nowrap gap-1">
                                    <a href="{{ route('acervo.arquivo', $doc->cod_documento) }}" target="_blank" rel="noopener" class="btn btn-icon btn-outline-info" title="Abrir PDF em nova aba">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                    <a href="{{ route('acervo.arquivo', ['documento' => $doc->cod_documento, 'baixar' => 1]) }}" class="btn btn-icon btn-outline-secondary" title="Baixar arquivo">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    @if($doc->dsc_link)
                                        <a href="{{ $doc->dsc_link }}" target="_blank" rel="noopener noreferrer" class="btn btn-icon btn-outline-secondary" title="Abrir link oficial (fora do sistema)">
                                            <i class="bi bi-link-45deg"></i>
                                        </a>
                                    @endif
                                    @can('update', $doc)
                                        <x-action-button variant="outline-primary" icon="pencil" tooltip="Editar" wire:click="edit('{{ $doc->cod_documento }}')" class="btn-action-icon" />
                                    @endcan
                                    @can('delete', $doc)
                                        <x-action-button variant="outline-danger" icon="trash" tooltip="Excluir" wire:click="confirmDelete('{{ $doc->cod_documento }}')" class="btn-action-icon" />
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-5">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="bi bi-folder2"></i></div>
                                    <h5 class="empty-state-title">Nenhum documento encontrado</h5>
                                    <p class="empty-state-text">
                                        @if($search !== '' || $filtroTipo !== '' || $filtroPei !== '' || $filtroAno !== '')
                                            Nenhum documento atende aos filtros escolhidos.
                                        @elseif($podeEnviar)
                                            Envie o primeiro PDF do acervo.
                                        @else
                                            Ainda não há documentos disponíveis para você.
                                        @endif
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($documentos->hasPages())
            <div class="card-footer pagination-footer">
                <span class="pagination-info">Mostrando <span class="fw-semibold">{{ $documentos->firstItem() }}</span> a <span class="fw-semibold">{{ $documentos->lastItem() }}</span> de <span class="fw-semibold">{{ $documentos->total() }}</span></span>
                {{ $documentos->onEachSide(1)->links() }}
            </div>
        @endif
    </div>

    {{-- Enviar / editar --}}
    @if($showModal)
        <div class="modal fade show" tabindex="-1" role="dialog" style="display: block; background: rgba(0,0,0,0.5); z-index: 1055;">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="modal-header gradient-theme-header text-white border-0 py-3 px-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-circle-mini bg-white bg-opacity-25 text-white"><i class="bi bi-file-earmark-pdf"></i></div>
                            <div>
                                <h5 class="modal-title fw-bold mb-0">{{ $documentoId ? 'Editar documento' : 'Enviar documento' }}</h5>
                                <p class="mb-0 small text-white-50">Campos com <span class="text-warning">*</span> são obrigatórios</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" wire:click="fecharModal" aria-label="Fechar"></button>
                    </div>

                    <form wire:submit.prevent="save">
                        <div class="modal-body p-4 bg-white">
                            <div class="row g-3">
                                {{-- Arquivo --}}
                                <div class="col-12">
                                    <label for="doc-arquivo" class="form-label small text-uppercase fw-bold text-muted">
                                        Arquivo PDF @if(!$documentoId)<span class="text-danger">*</span>@endif
                                    </label>
                                    <input type="file" id="doc-arquivo" accept="application/pdf,.pdf" wire:model="arquivo" class="form-control @error('arquivo') is-invalid @enderror">
                                    <div wire:loading wire:target="arquivo" class="small text-primary mt-1">
                                        <span class="spinner-border spinner-border-sm me-1"></span>Carregando o arquivo…
                                    </div>
                                    @error('arquivo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <div class="form-text">
                                        Somente PDF, até 20 MB.
                                        @if($documentoId) Arquivo atual: <strong>{{ $arquivoAtual }}</strong> — escolha outro só se quiser substituí-lo. @endif
                                    </div>
                                </div>

                                <div class="col-md-8">
                                    <label for="doc-nome" class="form-label small text-uppercase fw-bold text-muted">Nome do documento <span class="text-danger">*</span></label>
                                    <input type="text" id="doc-nome" maxlength="255" wire:model="nom_documento" class="form-control @error('nom_documento') is-invalid @enderror" placeholder="Ex.: Portaria que aprova o PEI 2024-2027">
                                    @error('nom_documento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="doc-tipo-form" class="form-label small text-uppercase fw-bold text-muted">Tipo de documento <span class="text-danger">*</span></label>
                                    <select id="doc-tipo-form" wire:model="dsc_tipo" class="form-select @error('dsc_tipo') is-invalid @enderror">
                                        <option value="">Selecione…</option>
                                        @foreach(\App\Models\Documento::TIPOS as $grupo => $tipos)
                                            <optgroup label="{{ $grupo }}">
                                                @foreach($tipos as $tipo)
                                                    <option value="{{ $tipo }}">{{ $tipo }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                    @error('dsc_tipo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="doc-numero" class="form-label small text-uppercase fw-bold text-muted">Número</label>
                                    <input type="text" id="doc-numero" maxlength="60" wire:model="num_documento" class="form-control @error('num_documento') is-invalid @enderror" placeholder="Ex.: 123/2026">
                                    @error('num_documento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="doc-ano-ref" class="form-label small text-uppercase fw-bold text-muted">Ano de referência</label>
                                    <input type="number" id="doc-ano-ref" min="1900" max="2100" wire:model="num_ano_referencia" class="form-control @error('num_ano_referencia') is-invalid @enderror" placeholder="{{ now()->year }}">
                                    @error('num_ano_referencia') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="doc-data" class="form-label small text-uppercase fw-bold text-muted">Data do documento</label>
                                    <input type="date" id="doc-data" wire:model="dte_documento" class="form-control @error('dte_documento') is-invalid @enderror">
                                    @error('dte_documento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="doc-origem" class="form-label small text-uppercase fw-bold text-muted">Origem</label>
                                    <input type="text" id="doc-origem" maxlength="255" wire:model="dsc_origem" class="form-control @error('dsc_origem') is-invalid @enderror" placeholder="Órgão ou unidade que emitiu. Ex.: Casa Civil">
                                    @error('dsc_origem') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="doc-link" class="form-label small text-uppercase fw-bold text-muted">Link oficial</label>
                                    <input type="url" id="doc-link" maxlength="500" wire:model="dsc_link" class="form-control @error('dsc_link') is-invalid @enderror" placeholder="Ex.: publicação no Diário Oficial (https://...)">
                                    @error('dsc_link') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="doc-pei-form" class="form-label small text-uppercase fw-bold text-muted">PEI</label>
                                    <select id="doc-pei-form" wire:model="cod_pei" class="form-select @error('cod_pei') is-invalid @enderror">
                                        <option value="">Nenhum (não ligado a um ciclo)</option>
                                        @foreach($peis as $pei)
                                            <option value="{{ $pei->cod_pei }}">{{ $pei->dsc_pei }}</option>
                                        @endforeach
                                    </select>
                                    @error('cod_pei') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="doc-area" class="form-label small text-uppercase fw-bold text-muted">Área (unidade)</label>
                                    <select id="doc-area" wire:model="cod_organizacao" class="form-select @error('cod_organizacao') is-invalid @enderror">
                                        @if($podeInstitucional)
                                            <option value="">Institucional (vale para todo o órgão)</option>
                                        @endif
                                        @foreach($unidadesParaEnvio as $cod => $nome)
                                            <option value="{{ $cod }}">{{ $nome }}</option>
                                        @endforeach
                                    </select>
                                    @error('cod_organizacao') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <div class="form-text">Quem tem perfil na área (ou acima dela) vê o documento. Institucional: todos veem.</div>
                                </div>

                                <div class="col-12">
                                    <label for="doc-descricao" class="form-label small text-uppercase fw-bold text-muted">Ementa / descrição</label>
                                    <textarea id="doc-descricao" rows="3" maxlength="5000" wire:model="txt_descricao" class="form-control @error('txt_descricao') is-invalid @enderror" placeholder="Do que trata o documento"></textarea>
                                    @error('txt_descricao') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer border-0 p-4 bg-white">
                            <button type="button" class="btn btn-light px-4 rounded-pill fw-bold text-muted" wire:click="fecharModal">Cancelar</button>
                            <button type="submit" class="btn btn-primary gradient-theme-btn px-5 rounded-pill shadow-sm" wire:loading.attr="disabled" wire:target="save,arquivo">
                                <span wire:loading.remove wire:target="save"><i class="bi bi-check-lg me-2"></i>{{ $documentoId ? 'Salvar alterações' : 'Enviar documento' }}</span>
                                <span wire:loading wire:target="save"><span class="spinner-border spinner-border-sm me-2"></span>Gravando…</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Excluir --}}
    @if($showDeleteModal)
        <div class="modal fade show" tabindex="-1" role="dialog" style="display: block; background: rgba(0,0,0,0.5); z-index: 1055;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-body p-4 text-center">
                        <i class="bi bi-trash3 text-danger fs-1 d-block mb-3"></i>
                        <h5 class="fw-bold">Excluir documento?</h5>
                        <p class="text-muted mb-0">"{{ $nom_documento }}" sai do acervo e deixa de aparecer para todos. A exclusão fica registrada na auditoria.</p>
                    </div>
                    <div class="modal-footer border-0 justify-content-center pb-4">
                        <button type="button" class="btn btn-light px-4 rounded-pill" wire:click="fecharModal">Cancelar</button>
                        <button type="button" class="btn btn-danger px-4 rounded-pill" wire:click="delete" wire:loading.attr="disabled">Excluir</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
