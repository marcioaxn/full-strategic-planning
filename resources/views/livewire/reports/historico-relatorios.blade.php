<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('relatorios.index') }}" wire:navigate class="text-decoration-none">Relatórios</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Histórico</li>
                </ol>
            </nav>
            <h2 class="h4 fw-bold mb-0">Histórico de Relatórios Gerados</h2>
        </div>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Data de Geração</th>
                        <th>Tipo</th>
                        <th>Formato</th>
                        <th>Tamanho</th>
                        <th class="text-end pe-4">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($historico as $rel)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-bold d-block">{{ $rel->created_at->format('d/m/Y') }}</span>
                                <small class="text-muted">{{ $rel->created_at->format('H:i:s') }}</small>
                            </td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10">
                                    {{ ucfirst($rel->dsc_tipo_relatorio) }}
                                </span>
                            </td>
                            <td>
                                <span class="text-uppercase fw-bold small text-muted">{{ $rel->dsc_formato }}</span>
                            </td>
                            <td>
                                <small>{{ number_format($rel->num_tamanho_bytes / 1024, 1) }} KB</small>
                            </td>
                            <td class="text-end pe-4">
                                <button wire:click="download('{{ $rel->cod_relatorio_gerado }}')" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-download me-1"></i> Download
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                Nenhum relatório encontrado no seu histórico.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($historico->hasPages())
            <div class="card-footer bg-white">
                {{ $historico->links() }}
            </div>
        @endif
    </div>
</div>