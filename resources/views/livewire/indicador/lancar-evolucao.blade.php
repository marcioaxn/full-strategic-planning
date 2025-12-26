<div>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('indicadores.index') }}" class="text-decoration-none">Indicadores</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Lançar Evolução</li>
                    </ol>
                </nav>
                <h2 class="h4 fw-bold mb-0">Lançamento de Resultados</h2>
                <p class="text-muted small mb-0">{{ $indicador->nom_indicador }} ({{ $indicador->dsc_unidade_medida }})</p>
            </div>
        </div>
    </x-slot>

    @if (session()->has('status'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Coluna Lançamento -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-calendar-event me-2 text-primary"></i>Dados do Período</h5>
                </div>
                <div class="card-body p-4 pt-0">
                    <div class="row g-3 mb-4 p-3 bg-light rounded-3 border">
                        <div class="col-md-6">
                            <label class="form-label text-muted small text-uppercase fw-bold">Mês</label>
                            <select wire:model.live="mes" class="form-select">
                                @foreach([1=>'Janeiro', 2=>'Fevereiro', 3=>'Março', 4=>'Abril', 5=>'Maio', 6=>'Junho', 7=>'Julho', 8=>'Agosto', 9=>'Setembro', 10=>'Outubro', 11=>'Novembro', 12=>'Dezembro'] as $num => $nome)
                                    <option value="{{ $num }}">{{ $nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small text-uppercase fw-bold">Ano</label>
                            <select wire:model.live="ano" class="form-select">
                                @for($i = now()->year - 2; $i <= now()->year + 2; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <form wire:submit.prevent="salvar">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label text-muted small text-uppercase fw-bold">Valor Previsto</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" wire:model="vlr_previsto" class="form-control" placeholder="0,00">
                                    <span class="input-group-text bg-white small text-muted">{{ $indicador->dsc_unidade_medida }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small text-uppercase fw-bold">Valor Realizado</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" wire:model="vlr_realizado" class="form-control fw-bold" placeholder="0,00">
                                    <span class="input-group-text bg-white small text-muted">{{ $indicador->dsc_unidade_medida }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted small text-uppercase fw-bold">Análise do Desempenho / Comentários</label>
                            <textarea wire:model="txt_avaliacao" class="form-control" rows="5" placeholder="Explique os motivos do resultado, desvios ou ações corretivas..."></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted small text-uppercase fw-bold"><i class="bi bi-paperclip me-1"></i>Anexar Evidências (Arquivos)</label>
                            <input type="file" wire:model="arquivosTemporarios" class="form-control" multiple>
                            <div wire:loading wire:target="arquivosTemporarios" class="mt-2 text-primary small">
                                <span class="spinner-border spinner-border-sm me-1"></span> Carregando arquivos...
                            </div>
                            
                            @if($arquivosExistentes && count($arquivosExistentes) > 0)
                                <div class="mt-3">
                                    <p class="small fw-bold text-muted mb-2">Arquivos Enviados:</p>
                                    <div class="list-group list-group-flush border rounded-3">
                                        @foreach($arquivosExistentes as $arq)
                                            <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                                                <small class="text-truncate" style="max-width: 80%;"><i class="bi bi-file-earmark-check me-2"></i>{{ $arq->dsc_arquivo }}</small>
                                                <button type="button" wire:click="excluirArquivo('{{ $arq->cod_arquivo }}')" class="btn btn-link text-danger p-0"><i class="bi bi-x-circle"></i></button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="d-flex justify-content-between align-items-center border-top pt-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="blnAtualizado" wire:model="bln_atualizado" true-value="Sim" false-value="Não">
                                <label class="form-check-label small fw-bold" for="blnAtualizado">Marcar como Atualizado</label>
                            </div>
                            <button type="submit" class="btn btn-primary gradient-theme-btn px-5 py-2 fw-bold shadow-sm">
                                <i class="bi bi-save me-2"></i>Salvar Lançamento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Coluna Histórico -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i>Histórico Recente</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th class="ps-4">Mês/Ano</th>
                                    <th>Realizado</th>
                                    <th>Ating.</th>
                                    <th class="text-end pe-4">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($evolucoes as $ev)
                                    <tr>
                                        <td class="ps-4 py-3">
                                            <span class="fw-bold small">{{ $ev->getNomeMes() }} / {{ $ev->num_ano }}</span>
                                        </td>
                                        <td>
                                            <small class="fw-mono">{{ number_format($ev->vlr_realizado, 2, ',', '.') }}</small>
                                        </td>
                                        <td>
                                            @php $ating = $ev->calcularAtingimento(); @endphp
                                            <span class="fw-bold small text-{{ $ating >= 100 ? 'success' : ($ating >= 80 ? 'warning' : 'danger') }}">
                                                {{ number_format($ating, 1) }}%
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            @if($ev->bln_atualizado === 'Sim')
                                                <i class="bi bi-check-all text-success fs-5" title="Atualizado"></i>
                                            @else
                                                <i class="bi bi-dash-circle text-muted small" title="Pendente"></i>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center py-5 text-muted italic">Sem lançamentos anteriores.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>