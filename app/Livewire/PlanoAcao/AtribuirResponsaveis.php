<?php

namespace App\Livewire\PlanoAcao;

use App\Models\PEI\PlanoDeAcao;
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
        $this->responsaveis = DB::table('public.rel_users_tab_organizacoes_tab_perfil_acesso as pivot')
            ->join('users', 'users.id', '=', 'pivot.user_id')
            ->join('public.tab_perfil_acesso as perfil', 'perfil.cod_perfil', '=', 'pivot.cod_perfil')
            ->where('pivot.cod_plano_de_acao', $this->plano->cod_plano_de_acao)
            ->select('users.name', 'users.email', 'perfil.dsc_perfil', 'pivot.id', 'pivot.user_id', 'pivot.cod_perfil')
            ->get();

        // 2. Carregar Usuários da mesma Organização (para o select)
        $this->usuariosDisponiveis = User::whereHas('organizacoes', function($q) {
            $q->where('public.tab_organizacoes.cod_organizacao', $this->plano->cod_organizacao);
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
        $existe = DB::table('public.rel_users_tab_organizacoes_tab_perfil_acesso')
            ->where('cod_plano_de_acao', $this->plano->cod_plano_de_acao)
            ->where('user_id', $this->novo_usuario_id)
            ->where('cod_perfil', $this->novo_perfil_id)
            ->exists();

        if ($existe) {
            session()->flash('error', 'Este usuário já possui este perfil atribuído a este plano.');
            return;
        }

        // Inserir na pivot
        DB::table('public.rel_users_tab_organizacoes_tab_perfil_acesso')->insert([
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

        DB::table('public.rel_users_tab_organizacoes_tab_perfil_acesso')
            ->where('id', $pivotId)
            ->delete();

        $this->carregarDados();
        session()->flash('status', 'Vínculo removido.');
    }

    public function render()
    {
        return view('livewire.plano-acao.atribuir-responsaveis');
    }
}