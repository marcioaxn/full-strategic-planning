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
            'description' => 'Classic professional blue theme',
            'preview' => 'linear-gradient(135deg, #1B408E, #5375C2)', // Deep Blue -> Lightened Blue
        ],
        'info' => [
            'name' => 'Sky Cyan',
            'description' => 'Light and airy cyan',
            'preview' => 'linear-gradient(135deg, #0891b2, #67E8F9)', // Cyan 600 -> Cyan 300
        ],
        'success' => [
            'name' => 'Forest Green',
            'description' => 'Fresh and natural green',
            'preview' => 'linear-gradient(135deg, #059669, #6EE7B7)', // Emerald 600 -> Emerald 300
        ],
        'warning' => [
            'name' => 'Sunset Orange',
            'description' => 'Warm and energetic orange',
            'preview' => 'linear-gradient(135deg, #d97706, #FCD34D)', // Amber 600 -> Amber 300
        ],
        'secondary' => [
            'name' => 'Slate Gray',
            'description' => 'Sophisticated neutral gray',
            'preview' => 'linear-gradient(135deg, #475569, #A7B3C4)', // Slate 700 -> Lightened Slate
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
