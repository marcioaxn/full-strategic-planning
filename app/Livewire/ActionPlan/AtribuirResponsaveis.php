<?php

namespace App\Livewire\ActionPlan;

use App\Models\ActionPlan\PlanoComunicacao;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\ActionPlan\Raci;
use App\Models\User;
use App\Models\PerfilAcesso;
use App\Models\Organization;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

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
    public ?string $comunEditId = null;
    public array $formComun = [
        'nom_publico_alvo'  => '',
        'dsc_mensagem_chave'=> '',
        'dsc_canal'         => 'E-mail',
        'dsc_frequencia'    => 'Mensal',
        'nom_responsavel'   => '',
    ];

    // Matriz RACI
    public bool $showModalRaci = false;
    public ?string $raciEditId = null;
    public array $formRaci = [
        'user_id'     => '',
        'cod_entrega' => '',
        'dsc_papel'   => 'R',
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
        $this->responsaveis = DB::table('rel_users_tab_organizacoes_tab_perfil_acesso as pivot')
            ->join('users', 'users.id', '=', 'pivot.user_id')
            ->join('tab_perfil_acesso as perfil', 'perfil.cod_perfil', '=', 'pivot.cod_perfil')
            ->where('pivot.cod_plano_de_acao', $this->plano->cod_plano_de_acao)
            ->select('users.name', 'users.email', 'perfil.dsc_perfil', 'pivot.id', 'pivot.user_id', 'pivot.cod_perfil')
            ->get();

        // 2. Carregar Usuários da mesma Organização (para o select)
        $this->usuariosDisponiveis = User::whereHas('organizacoes', function($q) {
            $q->where('tab_organizacoes.cod_organizacao', $this->plano->cod_organizacao);
        })->orderBy('name')->get();
    }

    public function adicionar()
    {
        $this->authorize('update', $this->plano);

        $this->validate([
            'novo_usuario_id' => 'required|exists:users,id',
            'novo_perfil_id' => 'required|in:' . PerfilAcesso::GESTOR_RESPONSAVEL . ',' . PerfilAcesso::GESTOR_SUBSTITUTO,
        ]);

        // Verificar duplicata
        $existe = DB::table('rel_users_tab_organizacoes_tab_perfil_acesso')
            ->where('cod_plano_de_acao', $this->plano->cod_plano_de_acao)
            ->where('user_id', $this->novo_usuario_id)
            ->where('cod_perfil', $this->novo_perfil_id)
            ->exists();

        if ($existe) {
            session()->flash('error', 'Este usuário já possui este perfil atribuído a este plano.');
            return;
        }

        // Inserir na pivot
        DB::table('rel_users_tab_organizacoes_tab_perfil_acesso')->insert([
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
        $this->authorize('update', $this->plano);

        DB::table('rel_users_tab_organizacoes_tab_perfil_acesso')
            ->where('id', $pivotId)
            ->delete();

        $this->carregarDados();
        session()->flash('status', 'Vínculo removido.');
    }

    // ── Plano de Comunicação ──────────────────────────────────────────────────

    public function novaComunicacao(): void
    {
        $this->comunEditId = null;
        $this->formComun = ['nom_publico_alvo' => '', 'dsc_mensagem_chave' => '', 'dsc_canal' => 'E-mail', 'dsc_frequencia' => 'Mensal', 'nom_responsavel' => ''];
        $this->showModalComun = true;
    }

    public function editarComunicacao(string $id): void
    {
        $c = PlanoComunicacao::findOrFail($id);
        $this->comunEditId = $id;
        $this->formComun = [
            'nom_publico_alvo'   => $c->nom_publico_alvo,
            'dsc_mensagem_chave' => $c->dsc_mensagem_chave,
            'dsc_canal'          => $c->dsc_canal,
            'dsc_frequencia'     => $c->dsc_frequencia,
            'nom_responsavel'    => $c->nom_responsavel ?? '',
        ];
        $this->showModalComun = true;
    }

    public function salvarComunicacao(): void
    {
        $this->validate([
            'formComun.nom_publico_alvo'   => 'required|string|max:150',
            'formComun.dsc_mensagem_chave' => 'required|string|max:500',
            'formComun.dsc_canal'          => 'required|string',
            'formComun.dsc_frequencia'     => 'required|string',
        ], [
            'formComun.nom_publico_alvo.required'   => 'Informe o público-alvo.',
            'formComun.dsc_mensagem_chave.required' => 'Informe a mensagem-chave.',
        ]);

        $data = array_merge($this->formComun, ['cod_plano_de_acao' => $this->plano->cod_plano_de_acao]);

        $this->comunEditId
            ? PlanoComunicacao::findOrFail($this->comunEditId)->update($data)
            : PlanoComunicacao::create($data);

        $this->showModalComun = false;
        $this->comunEditId    = null;
        $this->dispatch('notify', message: 'Item de comunicação salvo.', style: 'success');
    }

    public function excluirComunicacao(string $id): void
    {
        PlanoComunicacao::findOrFail($id)->delete();
        $this->dispatch('notify', message: 'Item removido.', style: 'warning');
    }

    // ── Matriz RACI ───────────────────────────────────────────────────────────

    public function novoRaci(): void
    {
        $this->raciEditId = null;
        $this->formRaci = ['user_id' => '', 'cod_entrega' => '', 'dsc_papel' => 'R'];
        $this->showModalRaci = true;
    }

    public function editarRaci(string $id): void
    {
        $r = Raci::findOrFail($id);
        $this->raciEditId = $id;
        $this->formRaci = [
            'user_id'     => $r->user_id,
            'cod_entrega' => $r->cod_entrega ?? '',
            'dsc_papel'   => $r->dsc_papel,
        ];
        $this->showModalRaci = true;
    }

    public function salvarRaci(): void
    {
        $this->authorize('update', $this->plano);

        $this->validate([
            'formRaci.user_id'   => 'required|exists:users,id',
            'formRaci.dsc_papel' => 'required|in:R,A,C,I',
        ], [
            'formRaci.user_id.required' => 'Selecione o usuário.',
        ]);

        $data = [
            'cod_plano_de_acao' => $this->plano->cod_plano_de_acao,
            'cod_entrega'       => $this->formRaci['cod_entrega'] ?: null,
            'user_id'           => $this->formRaci['user_id'],
            'dsc_papel'         => $this->formRaci['dsc_papel'],
        ];

        $this->raciEditId
            ? Raci::findOrFail($this->raciEditId)->update($data)
            : Raci::create($data);

        $this->showModalRaci = false;
        $this->raciEditId    = null;
        $this->dispatch('notify', message: 'Papel RACI salvo.', style: 'success');
    }

    public function excluirRaci(string $id): void
    {
        Raci::findOrFail($id)->delete();
        $this->dispatch('notify', message: 'Papel RACI removido.', style: 'warning');
    }

    public function render()
    {
        try {
            $comunicacoes = PlanoComunicacao::where('cod_plano_de_acao', $this->plano->cod_plano_de_acao)
                ->orderBy('num_ordem')->get();
        } catch (\Exception) {
            $comunicacoes = collect();
        }

        try {
            $racis = Raci::with(['usuario', 'entrega'])
                ->where('cod_plano_de_acao', $this->plano->cod_plano_de_acao)
                ->get()
                ->groupBy('dsc_papel');
        } catch (\Exception) {
            $racis = collect();
        }

        $entregas = $this->plano->entregas()->whereNull('cod_entrega_pai')->orderBy('num_ordem')->get();

        return view('livewire.plano-acao.atribuir-responsaveis', [
            'comunicacoes' => $comunicacoes,
            'canais'       => PlanoComunicacao::CANAIS,
            'frequencias'  => PlanoComunicacao::FREQUENCIAS,
            'racis'        => $racis,
            'papeisRaci'   => Raci::PAPEIS,
            'entregasPlano'=> $entregas,
        ]);
    }
}
