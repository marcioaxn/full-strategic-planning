<?php

namespace App\Livewire\Shared;

use App\Models\PEI\PEI;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class SeletorPEI extends Component
{
    public $peis;
    public $selecionadoId;

    public function mount()
    {
        $this->carregarPEIs();

        // Inicializar com a sessão
        $this->selecionadoId = Session::get('pei_selecionado_id');

        // Se não houver PEI na sessão após o login, define o padrão mas não redireciona (evita loop e erro de retorno)
        if (!$this->selecionadoId && $this->peis->isNotEmpty()) {
            $peiAtivo = $this->peis->first(fn($p) => $p->isAtivo()) ?? $this->peis->first();
            $this->definirSessao($peiAtivo);
        }
    }

    public function carregarPEIs()
    {
        $this->peis = PEI::orderBy('num_ano_inicio_pei', 'desc')->get();
    }

    /**
     * Apenas define os dados na sessão
     */
    private function definirSessao(PEI $pei)
    {
        $this->selecionadoId = $pei->cod_pei;
        Session::put('pei_selecionado_id', $pei->cod_pei);
        Session::put('pei_selecionado_dsc', $pei->dsc_pei);
        Session::put('pei_selecionado_periodo', $pei->num_ano_inicio_pei . '-' . $pei->num_ano_fim_pei);
    }

    /**
     * Ação disparada pelo clique do usuário no dropdown
     */
    public function selecionar($id)
    {
        $pei = PEI::find($id);

        if ($pei) {
            $this->definirSessao($pei);

            // Dispara evento global para componentes na mesma página
            $this->dispatch('peiSelecionado', id: $id);

            // Recarrega a página atual para garantir consistência total dos dados
            // Usamos o header Referer para voltar à mesma URL, respeitando wire:navigate
            $url = request()->header('Referer') ?? route('dashboard');
            return $this->redirect($url, navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.shared.seletor-pei');
    }
}
