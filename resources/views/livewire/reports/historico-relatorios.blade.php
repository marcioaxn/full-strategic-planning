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

    {{-- Texto humanizado: o gestor não conseguiu explicar esta tela aos clientes,
         e com razão — ela estava vazia por construção. --}}
    <div class="alert alert-light border d-flex gap-3 mb-4">
        <i class="bi bi-info-circle text-primary fs-5"></i>
        <div>
            <strong>O que é esta tela.</strong>
            Registro dos relatórios que <strong>você</strong> gerou: data, tipo, formato e os
            filtros usados. Serve para reencontrar um relatório que você apresentou e refazê-lo
            com os mesmos critérios.
            <div class="small text-muted mt-2">
                O arquivo não fica guardado — o relatório é gerado na hora, sempre com os dados
                atualizados. Aqui ficam o registro e os filtros.
            </div>
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
                        {{-- O texto acima promete que "aqui ficam o registro e os
                             filtros" — mas os filtros não eram exibidos em lugar
                             nenhum. Sem eles, o registro não serve para refazer
                             nada, que é a única utilidade que a tela tem. --}}
                        <th>Filtros aplicados</th>
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
                            <td style="max-width: 320px;">
                                @php $filtros = collect($rel->txt_filtros_aplicados ?? [])->filter(fn ($v) => is_scalar($v) && $v !== ''); @endphp
                                @if($filtros->isEmpty())
                                    <small class="text-muted fst-italic">Sem filtros — ciclo e organização da sessão.</small>
                                @else
                                    @foreach($filtros as $chave => $valor)
                                        <span class="badge bg-light text-dark border fw-normal me-1 mb-1">
                                            {{ str_replace('_', ' ', $chave) }}: <strong>{{ $valor }}</strong>
                                        </span>
                                    @endforeach
                                @endif
                            </td>
                            {{--
                                🔴 A AÇÃO PRECISA DIZER O QUE FAZ.

                                Havia um botão "Download" em TODA linha. Só que o
                                relatório que o cliente baixa clicando na tela não
                                guarda arquivo: ele vai direto para o navegador, e o
                                registro nasce sem caminho. O clique devolvia "o
                                arquivo não está mais disponível" — enquanto o texto
                                no topo da própria tela já avisava que arquivo não
                                fica guardado. A tela contradizia a si mesma.

                                Agora: quem TEM arquivo (relatório agendado) baixa;
                                quem não tem, gera de novo com os mesmos filtros.
                            --}}
                            <td class="text-end pe-4">
                                @if($rel->temArquivoGuardado())
                                    <button wire:click="download('{{ $rel->cod_relatorio_gerado }}')" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-download me-1"></i> Baixar arquivo
                                    </button>
                                @elseif($url = $rel->urlParaRegerar())
                                    <a href="{{ $url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-arrow-clockwise me-1"></i> Gerar de novo
                                    </a>
                                @else
                                    <span class="small text-muted">Registro histórico</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-clock-history fs-3 d-block mb-2 opacity-50"></i>
                                Você ainda não gerou nenhum relatório.<br>
                                <small>Assim que gerar o primeiro em
                                    <a href="{{ route('relatorios.index') }}" wire:navigate>Relatórios</a>,
                                    ele aparece aqui.</small>
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