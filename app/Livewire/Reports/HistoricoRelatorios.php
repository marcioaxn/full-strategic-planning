<?php

namespace App\Livewire\Reports;

use App\Models\Reports\RelatorioGerado;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

#[Layout('layouts.app')]
class HistoricoRelatorios extends Component
{
    use WithPagination;

    public function download($id)
    {
        $relatorio = RelatorioGerado::findOrFail($id);
        
        if (!Storage::disk('public')->exists($relatorio->dsc_caminho_arquivo)) {
            session()->flash('error', 'Arquivo não encontrado.');
            return;
        }

        return Storage::disk('public')->download($relatorio->dsc_caminho_arquivo);
    }

    public function render()
    {
        $historico = RelatorioGerado::where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('livewire.reports.historico-relatorios', [
            'historico' => $historico
        ]);
    }
}