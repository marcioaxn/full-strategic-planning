<div>
    <div class="leads-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="icon-circle-header gradient-theme-icon">
                    <i class="bi bi-people-fill"></i>
                </div>
                <h1 class="h3 fw-bold mb-0">Papéis e responsabilidades</h1>
            </div>
            <p class="text-muted mb-0">Quem pode cadastrar, editar e lançar a evolução de cada parte do sistema.</p>
        </div>
    </div>

    {{-- A sutileza que ninguém adivinha, dita antes da tabela --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-key text-primary me-2"></i>A regra que mais gera dúvida</h5>

            <p class="mb-3">
                <strong>“Gestor Responsável” não é um crachá geral — é um vínculo com uma Iniciativa específica.</strong>
                A mesma pessoa pode ser Gestora Responsável da Iniciativa A e não ter papel nenhum na Iniciativa B.
                Ela consegue lançar a evolução de um indicador e não de outro, <em>na mesma tela</em>.
                Não é defeito: é o recorte de responsabilidade.
            </p>

            <div class="alert alert-warning border-0 mb-0">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Um usuário com o perfil <strong>Gestor Responsável</strong> que não esteja vinculado a
                nenhuma Iniciativa não conseguirá lançar evolução em lugar nenhum. Depois de criar o usuário,
                o Administrador da unidade o vincula em <strong>Iniciativas → Gestores e Responsáveis</strong>,
                em cada Iniciativa pela qual ele responde.
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-layers text-primary me-2"></i>Como a permissão é decidida</h5>
            <p class="text-muted mb-3">Três camadas. Todas precisam permitir; qualquer uma pode negar.</p>

            <div class="row g-3">
                <div class="col-md-4">
                    <div class="p-3 border rounded-3 h-100">
                        <span class="badge bg-primary mb-2">1</span>
                        <h6 class="fw-bold">O seu perfil</h6>
                        <p class="small text-muted mb-0">Define o que o papel pode fazer em cada módulo. É a tabela abaixo.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 border rounded-3 h-100">
                        <span class="badge bg-primary mb-2">2</span>
                        <h6 class="fw-bold">A sua unidade</h6>
                        <p class="small text-muted mb-0">
                            Cada perfil vale na unidade em que foi dado. Administrador e Consulta valem também nas unidades
                            abaixo dela; Gestores, só na própria. Ser Administrador numa unidade não dá poder nenhum em outra.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 border rounded-3 h-100">
                        <span class="badge bg-primary mb-2">3</span>
                        <h6 class="fw-bold">A titularidade</h6>
                        <p class="small text-muted mb-0">Para o Gestor Responsável e o Substituto: você responde por <em>esta</em> Iniciativa especificamente?</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-info-circle text-primary me-2"></i>Três regras que completam a tabela</h5>
            <ul class="mb-0">
                <li class="mb-2">
                    <strong>O que é da instituição inteira</strong> — perspectivas, objetivos estratégicos, faixas do farol
                    (graus de satisfação), cadeia de valor e a abertura do ciclo (Inaugurar e Integrar) — só o
                    <strong>Super Administrador</strong> ou o <strong>Administrador da unidade raiz</strong> alteram.
                    Uma mudança ali vale para todas as unidades.
                </li>
                <li class="mb-2">
                    <strong>Consulta</strong> é o perfil de quem acompanha: abre as telas da unidade (e das abaixo dela)
                    e exporta relatórios, mas não cadastra, não altera e não exclui nada.
                </li>
                <li>
                    <strong>Contas são criadas só por quem tem permissão</strong>, na tela de Usuários: não há autocadastro.
                    <strong>Conta sem perfil</strong> não entra na área restrita
                    até um administrador vinculá-la a uma unidade com um perfil.
                </li>
            </ul>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0 p-4 pb-0">
            <h5 class="fw-bold mb-1"><i class="bi bi-table text-primary me-2"></i>O que cada perfil pode</h5>
            <p class="small text-muted mb-0">
                Esta tabela é gerada a partir da configuração real de permissões do sistema — não é um texto
                mantido à mão. O que você lê aqui é o que o sistema faz.
            </p>
        </div>
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="min-width: 260px;">Módulo</th>
                            @foreach($perfis as $nome)
                                <th class="text-center" style="min-width: 150px;">{{ $nome }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($linhas as $linha)
                            <tr>
                                <td>
                                    <span class="fw-bold">{{ $linha['rotulo'] }}</span>
                                    @if($linha['restrito'])
                                        <span class="badge bg-dark ms-1" title="Só o Super Administrador acessa">restrito</span>
                                    @endif
                                </td>
                                @foreach(array_keys($perfis) as $codPerfil)
                                    @php $caps = $linha['celulas'][$codPerfil]; @endphp
                                    <td class="text-center">
                                        @if(empty($caps))
                                            <span class="text-muted" title="Sem acesso">—</span>
                                        @else
                                            <div class="d-flex flex-wrap gap-1 justify-content-center">
                                                @foreach($abilities as $ability)
                                                    @if(in_array($ability, $caps, true))
                                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25"
                                                              title="{{ $descricoes[$ability] ?? $ability }}">
                                                            {{ $ability }}
                                                        </span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <h6 class="fw-bold mb-2">O que cada permissão significa</h6>
                <div class="row g-2">
                    @foreach($descricoes as $ability => $descricao)
                        <div class="col-md-6">
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border me-2">{{ $ability }}</span>
                            <span class="small text-muted">{{ $descricao }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
