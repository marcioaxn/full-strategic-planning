<?php

namespace App\Livewire\StrategicPlanning;

use App\Models\StrategicPlanning\GrauSatisfacao;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\ObjetivoComentario;
use App\Services\IndicadorCalculoService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DetalharObjetivo extends Component
{
    public $objetivo;

    public $estatisticas = [];

    public $novoComentario = '';

    public function mount($id)
    {
        $this->carregarObjetivo($id);
    }

    public function carregarObjetivo($id)
    {
        $this->objetivo = Objetivo::with([
            'perspectiva.pei',
            'indicadores',
            'planosAcao.entregas',
            'futuroAlmejado',
            'comentarios.user',
        ])->findOrFail($id);

        $service = app(IndicadorCalculoService::class);
        $ano = session('ano_selecionado', date('Y'));

        $atingimento = $service->calcularAtingimentoObjetivo($this->objetivo, $ano);

        $this->estatisticas = [
            'atingimento' => $atingimento,
            'cor_farol' => $this->corDoFarol($atingimento),
            'qtd_indicadores' => $this->objetivo->indicadores->count(),
            'qtd_planos' => $this->objetivo->planosAcao->count(),
        ];
    }

    /**
     * O farol do objetivo.
     *
     * 🔴 Os cortes estavam ESCRITOS NO CÓDIGO — 100 / 70 / 40 — enquanto o Mapa
     * Estratégico usava a régua que a organização configurou em Graus de
     * Satisfação. O MESMO objetivo acendia de uma cor no mapa e de outra na
     * própria página de detalhe.
     *
     * É a divergência entre módulos que o CEO cobra: um número que muda de
     * juízo conforme a tela derruba a confiança na plataforma inteira. Quem
     * decide os cortes é o gestor; aqui não se inventa nenhum.
     */
    private function corDoFarol($valor): string
    {
        return GrauSatisfacao::corDe(
            (float) $valor,
            $this->objetivo->perspectiva?->cod_pei,
            (int) session('ano_selecionado', date('Y'))
        );
    }

    /**
     * 🔴 AUTORIZAÇÃO AQUI DENTRO, e não só na tela.
     *
     * Esta página passou a ser servida também na área pública de
     * transparência. Todo método público de um componente Livewire é
     * invocável direto do navegador, e a chamada vai para /livewire/update —
     * que NÃO passa pelo middleware `transparencia`, o que bloqueia verbos de
     * escrita apenas nas rotas. Esconder o formulário no Blade não protege
     * nada: sem esta guarda, um visitante anônimo comenta no objetivo.
     */
    public function postarComentario()
    {
        abort_unless(Auth::check(), 403);

        $this->validate(['novoComentario' => 'required|string|min:3']);

        ObjetivoComentario::create([
            'cod_objetivo' => $this->objetivo->cod_objetivo,
            'user_id' => Auth::id(),
            'dsc_comentario' => $this->novoComentario,
        ]);

        $this->novoComentario = '';
        $this->carregarObjetivo($this->objetivo->cod_objetivo);

        $this->dispatch('notify', message: 'Comentário postado!');
    }

    public function removerComentario($id)
    {
        abort_unless(Auth::check(), 403);

        $comentario = ObjetivoComentario::findOrFail($id);

        if ($comentario->user_id === Auth::id() || Auth::user()->isSuperAdmin()) {
            $comentario->delete();
            $this->carregarObjetivo($this->objetivo->cod_objetivo);
        }
    }

    public function render()
    {
        // Visitante da área pública recebe o layout público; quem está
        // autenticado continua vendo a aplicação com o menu de sempre.
        return view('livewire.p-e-i.detalhar-objetivo')
            ->layout(Auth::check() ? 'layouts.app' : 'layouts.public');
    }
}
