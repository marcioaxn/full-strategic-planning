<?php

namespace App\Livewire\Audit;

use OwenIt\Auditing\Models\Audit;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class DetalharLog extends Component
{
    public $log;

    public function mount($id)
    {
        $this->log = Audit::with('user')->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.audit.detalhar-log');
    }
}
