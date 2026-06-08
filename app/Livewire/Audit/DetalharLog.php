<?php

namespace App\Livewire\Audit;

use Livewire\Attributes\Layout;
use Livewire\Component;
use OwenIt\Auditing\Models\Audit;

#[Layout('layouts.app')]
class DetalharLog extends Component
{
    public $log;

    public function mount($id)
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403, 'Acesso restrito ao Super Administrador.');

        $this->log = Audit::with('user')->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.audit.detalhar-log');
    }
}
