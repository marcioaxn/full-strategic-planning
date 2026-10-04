<?php

namespace App\Livewire\ActionPlan;

use App\Models\ActionPlan\PlanoComunicacao;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\ActionPlan\Raci;
use App\Models\PerfilAcesso;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app')]
class AtribuirResponsaveis extends Component
{
    use AuthorizesRequests;

    public $plano;

    public $responsaveis = [];

    public $usuariosDisponiveis = [];

    public $novo_usuario_id;

    public $novo_perfil_id;

    public $perfisGestao = [];

    // Plano de Comunicação
    public bool $showModalComun = false;

    // Só o servidor define (editarComunicacao); o navegador não pode apontar para item de outro plano.
    #[Locked]
    public ?string $comunEditId = null;

    public array $formComun = [
        'nom_publico_alvo' => '',
        'dsc_mensagem_chave' => '',
        'dsc_canal' => 'E-mail',
        'dsc_frequencia' => 'Mensal',
        'nom_responsavel' => '',
    ];

    // Matriz RACI
    public bool $showModalRaci = false;

    // Só o servidor define (editarRaci); o navegador não pode apontar para papel de outro plano.
    #[Locked]
    public ?string $raciEditId = null;

    public array $formRaci = [
        'user_id' => '',
        'cod_entrega' => '',
        'dsc_papel' => 'R',
    ];

    protected $listeners = ['refresh' => '$refresh'];

    public function mount($planoId)
    {
        $this->plano = PlanoDeAcao::findOrFail($planoId);
        $this->authorize('update', $this->plano);

        $this->perfisGestao = [
            ['id' => PerfilAcesso::GESTOR_RESPONSAVEL, 'label' => 'Gestor Responsável'],
            ['id' => PerfilAcesso::GESTOR_SUBSTITUTO, 'label' => 'Gestor Substituto'],
        ];

        $this->carregarDados();
    }

    public function carregarDados()
    {
        // 1. Carregar Responsáveis Atuais
        // Buscamos na pivot table
        $this->responsaveis = DB::table('organization.rel_users_tab_organizacoes_tab_perfil_acesso as pivot')
            ->join('pei.users as users', 'users.id', '=', 'pivot.user_id')
            ->join('organization.tab_perfil_acesso as perfil', 'perfil.cod_perfil', '=', 'pivot.cod_perfil')
            ->where('pivot.cod_plano_de_acao', $this->plano->cod_plano_de_acao)
            ->whereNull('pivot.deleted_at')
            ->select('users.name', 'users.email', 'perfil.dsc_perfil', 'pivot.id', 'pivot.user_id', 'pivot.cod_perfil')
            ->get();

        // 2. Usuários que podem receber o papel: os da unidade da iniciativa,
        // menos quem está designando (ninguém se designa a si mesmo).
        $this->usuariosDisponiveis = $this->consultaUsuariosDisponiveis()->orderBy('name')->get();
    }

    private function consultaUsuariosDisponiveis()
    {
        return User::whereHas('organizacoes', function ($q) {
            $q->where('tab_organizacoes.cod_organizacao', $this->plano->cod_organizacao);
        })->where('users.id', '!=', auth()->id());
    }

    public function adicionar()
    {
        // 🔴 Antes bastava poder EDITAR a iniciativa: o Gestor Substituto se
        // promovia a Responsável, ou dava o papel a qualquer conta do sistema.
        // Designar gestor é ato de quem administra a unidade da iniciativa.
        $this->authorize('designarGestores', $this->plano);

        $this->validate([
            'novo_usuario_id' => 'required|exists:users,id',
            'novo_perfil_id' => 'required|in:'.PerfilAcesso::GESTOR_RESPONSAVEL.','.PerfilAcesso::GESTOR_SUBSTITUTO,
        ]);

        // O id vem do navegador: só vale quem está na lista oferecida pela tela.
        if (! $this->consultaUsuariosDisponiveis()->where('users.id', $this->novo_usuario_id)->exists()) {
            $this->addError('novo_usuario_id', 'Escolha um usuário da unidade da iniciativa (e não você mesmo).');

            return;
        }

        // Uma pessoa tem UM papel de gestão na iniciativa: Responsável e
        // Substituto ao mesmo tempo anula o substituto, que existe para cobrir
        // a ausência do titular (achado no teste pelo navegador de 04/10/2026).
        $papelAtual = DB::table('organization.rel_users_tab_organizacoes_tab_perfil_acesso')
            ->where('cod_plano_de_acao', $this->plano->cod_plano_de_acao)
            ->where('user_id', $this->novo_usuario_id)
            ->whereIn('cod_perfil', [PerfilAcesso::GESTOR_RESPONSAVEL, PerfilAcesso::GESTOR_SUBSTITUTO])
            ->whereNull('deleted_at')
            ->value('cod_perfil');

        if ($papelAtual) {
            $papel = $papelAtual === PerfilAcesso::GESTOR_RESPONSAVEL ? 'Gestor(a) Responsável' : 'Gestor(a) Substituto(a)';
            $this->addError('novo_usuario_id', "Esta pessoa já é {$papel} desta iniciativa. Para trocar o papel, remova o atual primeiro.");

            return;
        }

        // Inserir na pivot
        DB::table('organization.rel_users_tab_organizacoes_tab_perfil_acesso')->insert([
            'id' => Str::uuid(),
            'user_id' => $this->novo_usuario_id,
            'cod_organizacao' => $this->plano->cod_organizacao,
            'cod_perfil' => $this->novo_perfil_id,
            'cod_plano_de_acao' => $this->plano->cod_plano_de_acao,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->novo_usuario_id = null;
        $this->novo_perfil_id = null;
        $this->carregarDados();
        session()->flash('status', 'Responsável atribuído com sucesso!');
    }

    public function remover($pivotId)
    {
        $this->authorize('designarGestores', $this->plano);

        // O id vem do navegador: só sai o vínculo de gestor DESTE plano, nesta organização.
        // Sem o recorte, qualquer linha da pivot de perfis (inclusive Admin de outra
        // organização) poderia ser apagada por quem edita um único plano.
        DB::table('organization.rel_users_tab_organizacoes_tab_perfil_acesso')
            ->where('id', $pivotId)
            ->where('cod_plano_de_acao', $this->plano->cod_plano_de_acao)
            ->where('cod_organizacao', $this->plano->cod_organizacao)
            ->whereIn('cod_perfil', [PerfilAcesso::GESTOR_RESPONSAVEL, PerfilAcesso::GESTOR_SUBSTITUTO])
            ->delete();

        $this->carregarDados();
        session()->flash('status', 'Vínculo removido.');
    }

    // ── Plano de Comunicação ──────────────────────────────────────────────────

    public function novaComunicacao(): void
    {
        $this->authorize('update', $this->plano);

        $this->comunEditId = null;
        $this->formComun = ['nom_publico_alvo' => '', 'dsc_mensagem_chave' => '', 'dsc_canal' => 'E-mail', 'dsc_frequencia' => 'Mensal', 'nom_responsavel' => ''];
        $this->showModalComun = true;
    }

    public function editarComunicacao(string $id): void
    {
        $this->authorize('update', $this->plano);

        $c = $this->comunicacaoDoPlano($id);
        $this->comunEditId = $id;
        $this->formComun = [
            'nom_publico_alvo' => $c->nom_publico_alvo,
            'dsc_mensagem_chave' => $c->dsc_mensagem_chave,
            'dsc_canal' => $c->dsc_canal,
            'dsc_frequencia' => $c->dsc_frequencia,
            'nom_responsavel' => $c->nom_responsavel ?? '',
        ];
        $this->showModalComun = true;
    }

    public function salvarComunicacao(): void
    {
        $this->authorize('update', $this->plano);

        // Limites = tamanho das colunas varchar (texto maior era erro 500).
        $this->validate([
            'formComun.nom_publico_alvo' => 'required|string|max:150',
            'formComun.dsc_mensagem_chave' => 'required|string|max:500',
            // Sem "in:" da lista: a base tem canais gravados fora dela ("Reunião",
            // "Portal/Imprensa") e a edição desses itens não pode travar.
            'formComun.dsc_canal' => 'required|string|max:100',
            'formComun.dsc_frequencia' => 'required|string|max:50',
            'formComun.nom_responsavel' => 'nullable|string|max:100',
        ], [
            'formComun.nom_publico_alvo.required' => 'Informe o público-alvo.',
            'formComun.nom_publico_alvo.max' => 'O público-alvo aceita até 150 caracteres.',
            'formComun.dsc_mensagem_chave.required' => 'Informe a mensagem-chave.',
            'formComun.dsc_mensagem_chave.max' => 'A mensagem-chave aceita até 500 caracteres.',
            'formComun.dsc_canal.max' => 'O canal aceita até 100 caracteres.',
            'formComun.dsc_frequencia.max' => 'A frequência aceita até 50 caracteres.',
            'formComun.nom_responsavel.max' => 'O nome do responsável aceita até 100 caracteres.',
        ]);

        // Só os campos do formulário (o array vem do navegador).
        $data = [
            'nom_publico_alvo' => $this->formComun['nom_publico_alvo'],
            'dsc_mensagem_chave' => $this->formComun['dsc_mensagem_chave'],
            'dsc_canal' => $this->formComun['dsc_canal'],
            'dsc_frequencia' => $this->formComun['dsc_frequencia'],
            'nom_responsavel' => ($this->formComun['nom_responsavel'] ?? '') ?: null,
            'cod_plano_de_acao' => $this->plano->cod_plano_de_acao,
        ];

        $this->comunEditId
            ? $this->comunicacaoDoPlano($this->comunEditId)->update($data)
            : PlanoComunicacao::create($data);

        $this->showModalComun = false;
        $this->comunEditId = null;
        $this->dispatch('notify', message: 'Item de comunicação salvo.', style: 'success');
    }

    public function excluirComunicacao(string $id): void
    {
        $this->authorize('update', $this->plano);

        $this->comunicacaoDoPlano($id)->delete();
        $this->dispatch('notify', message: 'Item removido.', style: 'warning');
    }

    // ── Matriz RACI ───────────────────────────────────────────────────────────

    public function novoRaci(): void
    {
        $this->authorize('update', $this->plano);

        $this->raciEditId = null;
        $this->formRaci = ['user_id' => '', 'cod_entrega' => '', 'dsc_papel' => 'R'];
        $this->showModalRaci = true;
    }

    public function editarRaci(string $id): void
    {
        $this->authorize('update', $this->plano);

        $r = $this->raciDoPlano($id);
        $this->raciEditId = $id;
        $this->formRaci = [
            'user_id' => $r->user_id,
            'cod_entrega' => $r->cod_entrega ?? '',
            'dsc_papel' => $r->dsc_papel,
        ];
        $this->showModalRaci = true;
    }

    public function salvarRaci(): void
    {
        $this->authorize('update', $this->plano);

        $this->validate([
            'formRaci.user_id' => 'required|exists:users,id',
            'formRaci.dsc_papel' => 'required|in:R,A,C,I',
        ], [
            'formRaci.user_id.required' => 'Selecione o usuário.',
        ]);

        // O usuário vem do navegador: só vale quem está na lista que a tela oferece
        // (pessoas da unidade da iniciativa). exists:users aceitava qualquer conta.
        if (! $this->consultaUsuariosDisponiveis()->where('users.id', $this->formRaci['user_id'])->exists()) {
            $this->addError('formRaci.user_id', 'Escolha uma pessoa da unidade da iniciativa.');

            return;
        }

        // A entrega escolhida também vem do navegador: tem de ser deste plano.
        if ($this->formRaci['cod_entrega']
            && ! $this->plano->entregas()->where('cod_entrega', $this->formRaci['cod_entrega'])->exists()) {
            abort(403);
        }

        $data = [
            'cod_plano_de_acao' => $this->plano->cod_plano_de_acao,
            'cod_entrega' => $this->formRaci['cod_entrega'] ?: null,
            'user_id' => $this->formRaci['user_id'],
            'dsc_papel' => $this->formRaci['dsc_papel'],
        ];

        $this->raciEditId
            ? $this->raciDoPlano($this->raciEditId)->update($data)
            : Raci::create($data);

        $this->showModalRaci = false;
        $this->raciEditId = null;
        $this->dispatch('notify', message: 'Papel RACI salvo.', style: 'success');
    }

    public function excluirRaci(string $id): void
    {
        $this->authorize('update', $this->plano);

        $this->raciDoPlano($id)->delete();
        $this->dispatch('notify', message: 'Papel RACI removido.', style: 'warning');
    }

    /**
     * Item de comunicação pelo id vindo do navegador, restrito ao plano da tela.
     */
    private function comunicacaoDoPlano(string $id): PlanoComunicacao
    {
        return PlanoComunicacao::where('cod_plano_de_acao', $this->plano->cod_plano_de_acao)->findOrFail($id);
    }

    /**
     * Papel RACI pelo id vindo do navegador, restrito ao plano da tela.
     */
    private function raciDoPlano(string $id): Raci
    {
        return Raci::where('cod_plano_de_acao', $this->plano->cod_plano_de_acao)->findOrFail($id);
    }

    public function render()
    {
        // Sem try/catch: as tabelas existem (migrations aplicadas) e uma falha
        // de verdade não pode virar "lista vazia" em silêncio.
        $comunicacoes = PlanoComunicacao::where('cod_plano_de_acao', $this->plano->cod_plano_de_acao)
            ->orderBy('num_ordem')->get();

        $racis = Raci::with(['usuario', 'entrega'])
            ->where('cod_plano_de_acao', $this->plano->cod_plano_de_acao)
            ->get()
            ->groupBy('dsc_papel');

        $entregas = $this->plano->entregas()->whereNull('cod_entrega_pai')->orderBy('num_ordem')->get();

        return view('livewire.plano-acao.atribuir-responsaveis', [
            'comunicacoes' => $comunicacoes,
            'canais' => PlanoComunicacao::CANAIS,
            'frequencias' => PlanoComunicacao::FREQUENCIAS,
            'racis' => $racis,
            'papeisRaci' => Raci::PAPEIS,
            'entregasPlano' => $entregas,
        ]);
    }
}
