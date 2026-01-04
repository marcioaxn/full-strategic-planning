<?php

namespace App\Livewire\Profile;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class UpdateThemeColorForm extends Component
{
    public $themeColor;
    public $previousThemeColor;

    public $availableColors = [
        'primary' => [
            'name' => 'Ocean Blue',
            'description' => 'Deep professional corporate blue',
            'preview' => 'linear-gradient(135deg, #1B408E, #4361EE)',
        ],
        'info' => [
            'name' => 'Electric Cyan',
            'description' => 'Vibrant and modern tech cyan',
            'preview' => 'linear-gradient(135deg, #0891B2, #06B6D4)',
        ],
        'success' => [
            'name' => 'Emerald Green',
            'description' => 'Fresh and high-contrast green',
            'preview' => 'linear-gradient(135deg, #059669, #10B981)',
        ],
        'warning' => [
            'name' => 'Golden Amber',
            'description' => 'Warm and energetic amber glow',
            'preview' => 'linear-gradient(135deg, #D97706, #F59E0B)',
        ],
        'secondary' => [
            'name' => 'Steel Slate',
            'description' => 'Sophisticated and neutral gray',
            'preview' => 'linear-gradient(135deg, #475569, #94A3B8)',
        ],
    ];

    public function mount()
    {
        $this->themeColor = Auth::user()->theme_color ?? 'primary';
        $this->previousThemeColor = $this->themeColor;
    }

    public function updated($propertyName)
    {
        // Auto-save when theme color changes
        if ($propertyName === 'themeColor' && $this->themeColor !== $this->previousThemeColor) {
            $this->updateThemeColor();
        }
    }

    public function updateThemeColor()
    {
        $this->validate([
            'themeColor' => 'required|in:primary,secondary,success,warning,info',
        ]);

        Auth::user()->update([
            'theme_color' => $this->themeColor,
        ]);

        $this->previousThemeColor = $this->themeColor;

        $this->dispatch('theme-updated', themeColor: $this->themeColor);
        $this->dispatch('saved');
    }

    public function render()
    {
        return view('livewire.profile.update-theme-color-form');
    }
}
