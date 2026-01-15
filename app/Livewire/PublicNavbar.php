<?php

namespace App\Livewire;

use Livewire\Component;

class PublicNavbar extends Component
{
    // The Navbar is a static container for the selectors.
    // It does NOT need to listen to selection events or re-render.
    // The Selectors (children) update themselves, and the Map (sibling) updates itself.
    
    public function render()
    {
        return view('livewire.public-navbar');
    }
}