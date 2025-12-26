<?php

namespace App\Livewire\PEI;

use App\Models\PEI\PEI;
use App\Models\PEI\Perspectiva;
use App\Models\Organization;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class MapaEstrategico extends Component
{
    public $perspectivas = [];
    public $peiAtivo;
    public $organizacaoId;
    public $organizacaoNome;

    protected $listeners = [
        'organizacaoSelecionada' => 'atualizarOrganizacao'
    ];

    public function mount()
    {
        // Se estiver logado, usa a organização da sessão. 
        // Se não, tenta pegar a Unidade Central ou a primeira disponível para visualização pública.
        if (Auth::check()) {
            $this->organizacaoId = Session::get('organizacao_selecionada_id');
        } else {
            // Unidade Central padrão para o público (UUID fixo da migration)
            $this->organizacaoId = '3834910f-66f7-46d8-9104-2904d59e1241';
        }

        $this->peiAtivo = PEI::ativos()->first();
        $this->atualizarOrganizacao($this->organizacaoId);
    }

    public function atualizarOrganizacao($id)
    {
        $this->organizacaoId = $id;
        
        if ($id) {
            $org = Organization::find($id);
            $this->organizacaoNome = $org ? $org->nom_organizacao : 'Sistema SEAE';
        }

        if ($this->peiAtivo) {
            $this->carregarMapa();
        }
    }

    public function carregarMapa()
    {
        $this->perspectivas = Perspectiva::where('cod_pei', $this->peiAtivo->cod_pei)
            ->with(['objetivos' => function($query) {
                $query->with(['indicadores'])->ordenadoPorNivel();
            }])
            ->ordenadoPorNivel()
            ->get();
    }

    public function render()
    {
        // Define o layout dinamicamente: 'app' para logados, 'guest' para público
        $layout = Auth::check() ? 'layouts.app' : 'layouts.guest';

        return view('livewire.pei.mapa-estrategico')
            ->layout($layout);
    }
}
