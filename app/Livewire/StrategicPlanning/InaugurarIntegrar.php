<?php

namespace App\Livewire\StrategicPlanning;

use App\Models\StrategicPlanning\CalendarioEventoPei;
use App\Models\StrategicPlanning\InauguraPei;
use App\Models\StrategicPlanning\IntegracaoInstrumento;
use App\Models\StrategicPlanning\PEI;
use App\Models\Agenda2030\ODS;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Session;

#[Layout('layouts.app')]
class InaugurarIntegrar extends Component
{
    // ── Estado da aba ativa ──────────────────────────────────────────────────
    public string $abaAtiva = 'planejamento';

    // ── PEI e contexto ───────────────────────────────────────────────────────
    public $peiAtivo;

    // ── Aba 1: Planejar o Planejamento ───────────────────────────────────────
    public $inaugurar;
    public bool $showFormInaugurar = false;

    public array $formInaugurar = [
        'txt_equipe'          => '',
        'txt_diretrizes'      => '',
        'txt_metodologia'     => '',
        'txt_observacoes'     => '',
        'dte_inicio_processo' => '',
        'dte_fim_previsto'    => '',
        'bln_aprovado'        => false,
    ];

    // ── Aba 2: Integração com Instrumentos ──────────────────────────────────
    public bool $showFormIntegracao = false;
    public $integracaoEditId = null;

    public array $formIntegracao = [
        'dsc_instrumento'     => '',
        'dsc_tipo_instrumento'=> 'PPA',
        'txt_pontos_atencao'  => '',
        'txt_tarefas'         => '',
        'dsc_intensidade'     => 'Media',
        'num_ordem'           => 0,
    ];

    // ── Aba 3: Agenda 2030 (ODS) — aderência institucional ──────────────────
    public array $odsAderidos      = [];   // num_ods marcados
    public array $odsContribuicoes = [];   // num_ods => texto
    public array $odsIntensidades  = [];   // num_ods => Alta|Media|Baixa

    // ── Aba 4: Calendário de Eventos ────────────────────────────────────────
    public bool $showFormEvento = false;
    public $eventoEditId = null;

    public array $formEvento = [
        'dsc_titulo'       => '',
        'dsc_objetivo'     => '',
        'dte_evento'       => '',
        'dsc_participantes'=> '',
        'dsc_tipo_evento'  => 'Reunião',
        'bln_realizado'    => false,
    ];

    // ── Feedback ─────────────────────────────────────────────────────────────
    public bool $showSuccessModal = false;
    public string $successMessage = '';
    public bool $showDeleteModal  = false;
    public string $deleteTarget   = '';
    public string $deleteId       = '';

    protected $listeners = [
        'peiSelecionado' => 'atualizarPEI',
    ];

    public function mount(): void
    {
        $this->peiAtivo = PEI::find(Session::get('pei_selecionado_id')) ?? PEI::ativos()->first();
        $this->carregarInaugurar();
        $this->carregarAgenda();
    }

    public function atualizarPEI($id): void
    {
        $this->peiAtivo = PEI::find($id);
        $this->carregarInaugurar();
        $this->carregarAgenda();
        $this->resetForms();
    }

    // ── Planejar o Planejamento ──────────────────────────────────────────────

    private function carregarInaugurar(): void
    {
        $this->inaugurar = $this->peiAtivo
            ? InauguraPei::where('cod_pei', $this->peiAtivo->cod_pei)->first()
            : null;
    }

    public function editarInaugurar(): void
    {
        if ($this->inaugurar) {
            $this->formInaugurar = [
                'txt_equipe'          => $this->inaugurar->txt_equipe ?? '',
                'txt_diretrizes'      => $this->inaugurar->txt_diretrizes ?? '',
                'txt_metodologia'     => $this->inaugurar->txt_metodologia ?? '',
                'txt_observacoes'     => $this->inaugurar->txt_observacoes ?? '',
                'dte_inicio_processo' => $this->inaugurar->dte_inicio_processo?->format('Y-m-d') ?? '',
                'dte_fim_previsto'    => $this->inaugurar->dte_fim_previsto?->format('Y-m-d') ?? '',
                'bln_aprovado'        => $this->inaugurar->bln_aprovado ?? false,
            ];
        }
        $this->showFormInaugurar = true;
    }

    public function salvarInaugurar(): void
    {
        $this->validate([
            'formInaugurar.txt_equipe'      => 'required|string|max:1000',
            'formInaugurar.txt_diretrizes'  => 'nullable|string',
            'formInaugurar.dte_inicio_processo' => 'nullable|date',
            'formInaugurar.dte_fim_previsto'    => 'nullable|date|after_or_equal:formInaugurar.dte_inicio_processo',
        ], [
            'formInaugurar.txt_equipe.required'   => 'Informe a equipe de planejamento.',
            'formInaugurar.dte_fim_previsto.after_or_equal' => 'A data fim deve ser igual ou posterior à data de início.',
        ]);

        $data = array_merge($this->formInaugurar, ['cod_pei' => $this->peiAtivo->cod_pei]);

        if ($this->inaugurar) {
            $this->inaugurar->update($data);
        } else {
            $this->inaugurar = InauguraPei::create($data);
        }

        $this->showFormInaugurar = false;
        $this->successMessage    = 'Planejamento do Processo registrado com sucesso.';
        $this->showSuccessModal  = true;
    }

    // ── Integração com Instrumentos ─────────────────────────────────────────

    public function novaIntegracao(): void
    {
        $this->integracaoEditId = null;
        $this->formIntegracao   = ['dsc_instrumento' => '', 'dsc_tipo_instrumento' => 'PPA',
            'txt_pontos_atencao' => '', 'txt_tarefas' => '', 'dsc_intensidade' => 'Media', 'num_ordem' => 0];
        $this->showFormIntegracao = true;
    }

    public function editarIntegracao(string $id): void
    {
        $rec = IntegracaoInstrumento::findOrFail($id);
        $this->integracaoEditId = $id;
        $this->formIntegracao   = [
            'dsc_instrumento'      => $rec->dsc_instrumento,
            'dsc_tipo_instrumento' => $rec->dsc_tipo_instrumento,
            'txt_pontos_atencao'   => $rec->txt_pontos_atencao ?? '',
            'txt_tarefas'          => $rec->txt_tarefas ?? '',
            'dsc_intensidade'      => $rec->dsc_intensidade,
            'num_ordem'            => $rec->num_ordem,
        ];
        $this->showFormIntegracao = true;
    }

    public function salvarIntegracao(): void
    {
        $this->validate([
            'formIntegracao.dsc_instrumento'      => 'required|string|max:100',
            'formIntegracao.dsc_tipo_instrumento' => 'required|string',
            'formIntegracao.dsc_intensidade'      => 'required|in:Alta,Media,Baixa',
        ], [
            'formIntegracao.dsc_instrumento.required' => 'Informe o nome do instrumento.',
        ]);

        $data = array_merge($this->formIntegracao, ['cod_pei' => $this->peiAtivo->cod_pei]);

        if ($this->integracaoEditId) {
            IntegracaoInstrumento::findOrFail($this->integracaoEditId)->update($data);
        } else {
            IntegracaoInstrumento::create($data);
        }

        $this->showFormIntegracao = false;
        $this->integracaoEditId   = null;
        $this->successMessage     = 'Integração com instrumento salva.';
        $this->showSuccessModal   = true;
    }

    public function confirmarExclusaoIntegracao(string $id): void
    {
        $this->deleteTarget = 'integracao';
        $this->deleteId     = $id;
        $this->showDeleteModal = true;
    }

    // ── Agenda 2030 (ODS) — aderência institucional ─────────────────────────

    private function carregarAgenda(): void
    {
        $this->odsAderidos = $this->odsContribuicoes = $this->odsIntensidades = [];

        if (!$this->peiAtivo) {
            return;
        }

        try {
            $vinculos = $this->peiAtivo->ods()->get();
            $this->odsAderidos      = $vinculos->pluck('num_ods')->map(fn ($n) => (int) $n)->toArray();
            $this->odsContribuicoes = $vinculos->pluck('pivot.txt_contribuicao', 'num_ods')->toArray();
            $this->odsIntensidades  = $vinculos->pluck('pivot.dsc_intensidade', 'num_ods')->toArray();
        } catch (\Throwable $e) {
            // Tabela rel_pei_ods ainda não migrada — degrada graciosamente.
        }
    }

    public function toggleOdsAderencia(int $num): void
    {
        if (in_array($num, $this->odsAderidos)) {
            $this->odsAderidos = array_values(array_diff($this->odsAderidos, [$num]));
            unset($this->odsContribuicoes[$num], $this->odsIntensidades[$num]);
            return;
        }

        $this->odsAderidos[] = $num;
        $this->odsIntensidades[$num] = $this->odsIntensidades[$num] ?? 'Media';
    }

    public function salvarAgenda(): void
    {
        if (!$this->peiAtivo) {
            return;
        }

        $sync = [];
        foreach ($this->odsAderidos as $num) {
            $sync[(int) $num] = [
                'txt_contribuicao' => $this->odsContribuicoes[$num] ?? null,
                'dsc_intensidade'  => $this->odsIntensidades[$num] ?? 'Media',
            ];
        }

        $this->peiAtivo->ods()->sync($sync);

        $this->successMessage   = 'Aderência à Agenda 2030 atualizada com sucesso.';
        $this->showSuccessModal = true;
    }

    // ── Calendário de Eventos ────────────────────────────────────────────────

    public function novoEvento(): void
    {
        $this->eventoEditId = null;
        $this->formEvento   = ['dsc_titulo' => '', 'dsc_objetivo' => '', 'dte_evento' => '',
            'dsc_participantes' => '', 'dsc_tipo_evento' => 'Reunião', 'bln_realizado' => false];
        $this->showFormEvento = true;
    }

    public function editarEvento(string $id): void
    {
        $ev = CalendarioEventoPei::findOrFail($id);
        $this->eventoEditId = $id;
        $this->formEvento   = [
            'dsc_titulo'       => $ev->dsc_titulo,
            'dsc_objetivo'     => $ev->dsc_objetivo ?? '',
            'dte_evento'       => $ev->dte_evento?->format('Y-m-d') ?? '',
            'dsc_participantes'=> $ev->dsc_participantes ?? '',
            'dsc_tipo_evento'  => $ev->dsc_tipo_evento,
            'bln_realizado'    => $ev->bln_realizado,
        ];
        $this->showFormEvento = true;
    }

    public function salvarEvento(): void
    {
        $this->validate([
            'formEvento.dsc_titulo'   => 'required|string|max:200',
            'formEvento.dte_evento'   => 'required|date',
            'formEvento.dsc_tipo_evento' => 'required|string',
        ], [
            'formEvento.dsc_titulo.required' => 'Informe o título do evento.',
            'formEvento.dte_evento.required' => 'Informe a data do evento.',
        ]);

        $data = array_merge($this->formEvento, ['cod_pei' => $this->peiAtivo->cod_pei]);

        if ($this->eventoEditId) {
            CalendarioEventoPei::findOrFail($this->eventoEditId)->update($data);
        } else {
            CalendarioEventoPei::create($data);
        }

        $this->showFormEvento = false;
        $this->eventoEditId   = null;
        $this->successMessage = 'Evento registrado no calendário.';
        $this->showSuccessModal = true;
    }

    public function confirmarExclusaoEvento(string $id): void
    {
        $this->deleteTarget = 'evento';
        $this->deleteId     = $id;
        $this->showDeleteModal = true;
    }

    // ── Exclusão genérica ────────────────────────────────────────────────────

    public function executarExclusao(): void
    {
        match ($this->deleteTarget) {
            'integracao' => IntegracaoInstrumento::findOrFail($this->deleteId)->delete(),
            'evento'     => CalendarioEventoPei::findOrFail($this->deleteId)->delete(),
            default      => null,
        };

        $this->showDeleteModal = false;
        $this->deleteTarget    = '';
        $this->deleteId        = '';
        $this->dispatch('notify', message: 'Registro excluído.', style: 'warning');
    }

    private function resetForms(): void
    {
        $this->showFormInaugurar  = false;
        $this->showFormIntegracao = false;
        $this->showFormEvento     = false;
    }

    public function render()
    {
        $integracoes = $this->peiAtivo
            ? IntegracaoInstrumento::where('cod_pei', $this->peiAtivo->cod_pei)
                ->orderBy('num_ordem')->orderBy('dsc_tipo_instrumento')->get()
            : collect();

        $eventos = $this->peiAtivo
            ? CalendarioEventoPei::where('cod_pei', $this->peiAtivo->cod_pei)
                ->orderBy('dte_evento')->get()
            : collect();

        return view('livewire.p-e-i.inaugurar-integrar', [
            'integracoes'     => $integracoes,
            'eventos'         => $eventos,
            'tiposInstrumento'=> IntegracaoInstrumento::TIPOS,
            'intensidades'    => IntegracaoInstrumento::INTENSIDADES,
            'tiposEvento'     => CalendarioEventoPei::TIPOS_EVENTO,
            'todosOds'        => ODS::ordenado()->get(),
        ]);
    }
}
