<?php

namespace App\Livewire\Admin;

use App\Models\SystemSetting;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ConfiguracaoSistema extends Component
{
    public bool $aiEnabled = true;
    public string $aiProvider = 'gemini';
    public string $aiApiKey = '';
    
    // Controle de UI
    public bool $showKey = false;
    public string $connectionStatus = '';
    public string $connectionMessage = '';

    public function mount()
    {
        $this->aiEnabled = SystemSetting::getValue('ai_enabled', true);
        $this->aiProvider = SystemSetting::getValue('ai_provider', 'gemini');
        $hasKey = SystemSetting::where('key', 'ai_api_key')->whereNotNull('value')->exists();
        $this->aiApiKey = $hasKey ? '********' : '';
    }

    public function testConnection()
    {
        $this->connectionStatus = 'testing';
        $this->connectionMessage = 'Testando comunicação...';

        $keyToTest = $this->aiApiKey;
        if ($keyToTest === '********') {
            $keyToTest = SystemSetting::getValue('ai_api_key');
        }

        if (empty($keyToTest)) {
            $this->connectionStatus = 'error';
            $this->connectionMessage = 'Forneça uma chave de API para testar.';
            return;
        }

        $provider = match($this->aiProvider) {
            'gemini' => new \App\Services\AI\GeminiProvider($keyToTest),
            'openai' => new \App\Services\AI\OpenAiProvider($keyToTest),
            default => new \App\Services\AI\GeminiProvider($keyToTest),
        };

        $result = $provider->testConnection();

        if ($result['success']) {
            $this->connectionStatus = 'success';
            $this->connectionMessage = $result['message'];
        } else {
            $this->connectionStatus = 'error';
            $this->connectionMessage = $result['message'];
        }
    }

    public function saveAiSettings()
    {
        if ($this->aiEnabled) {
            if (empty($this->aiProvider)) {
                $this->addError('aiProvider', 'Selecione um provedor de IA.');
                return;
            }

            if (empty($this->aiApiKey)) {
                $this->addError('aiApiKey', 'A chave de API é obrigatória para habilitar a IA.');
                return;
            }
        }

        SystemSetting::setValue('ai_enabled', $this->aiEnabled);
        SystemSetting::setValue('ai_provider', $this->aiProvider);
        
        if ($this->aiApiKey !== '********' && !empty($this->aiApiKey)) {
            SystemSetting::setValue('ai_api_key', $this->aiApiKey);
        }

        session()->flash('status', 'Configurações de IA salvas com sucesso!');
    }

    public function render()
    {
        return view('livewire.admin.configuracao-sistema');
    }
}
