<?php

namespace App\Livewire\Reports;

use App\Concerns\BaixaRelatorioGerado;
use App\Models\Reports\RelatorioGerado;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class HistoricoRelatorios extends Component
{
    use BaixaRelatorioGerado, WithPagination;

    public function mount(): void
    {
        $this->authorize('viewAny', RelatorioGerado::class);
    }

    public function render()
    {
        $historico = RelatorioGerado::where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('livewire.reports.historico-relatorios', [
            'historico' => $historico,
        ]);
    }
}
