<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Session;

class PublicNavbar extends Component
{
    protected $listeners = [
        'organizacaoSelecionada' => '$refresh',
        'peiSelecionado' => '$refresh',
        'anoSelecionado' => '$refresh'
    ];

    public function render()
    {
        return view('livewire.public-navbar');
    }
}
