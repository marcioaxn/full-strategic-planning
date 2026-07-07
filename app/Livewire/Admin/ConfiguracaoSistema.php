<?php

namespace App\Livewire\Admin;

use App\Models\SystemSetting;
use App\Services\AI\GeminiProvider;
use App\Services\AI\VertexAiProvider;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ConfiguracaoSistema extends Component
{
    public string $aiProvider = 'gemini-studio';

    public string $aiApiKey = '';

    public string $aiModel = 'gemini-2.5-flash';

    public string $vertexProjectId = '';

    public string $vertexLocation = 'us-central1';

    public string $vertexServiceAccountJson = '';

    public string $connectionStatus = '';

    public string $connectionMessage = '';

    public function mount()
    {
        $this->authorize('modulo.acessar', 'admin.configuracoes');

        $this->aiProvider = SystemSetting::getValue('ai_provider', 'gemini-studio');

        $hasKey = SystemSetting::where('key', 'ai_api_key')->whereNotNull('value')->exists();
        $this->aiApiKey = $hasKey ? '********' : '';

        $this->aiModel = SystemSetting::getValue('ai_model', 'gemini-2.5-flash');

        $this->vertexProjectId = SystemSetting::getValue('vertex_project_id', '');
        $this->vertexLocation = SystemSetting::getValue('vertex_location', 'us-central1');

        $hasJson = SystemSetting::where('key', 'vertex_service_account_json')->whereNotNull('value')->exists();
        $this->vertexServiceAccountJson = $hasJson ? '********' : '';
    }

    public function testConnection()
    {
        $this->connectionStatus = 'testing';
        $this->connectionMessage = 'Testando comunicação com o Agente de IA...';

        if ($this->aiProvider === 'vertex-ai') {
            $jsonToTest = $this->vertexServiceAccountJson;
            if ($jsonToTest === '********') {
                $jsonToTest = SystemSetting::getValue('vertex_service_account_json');
            }

            if (empty($jsonToTest)) {
                $this->connectionStatus = 'error';
                $this->connectionMessage = 'JSON da Service Account não informado.';

                return;
            }

            $provider = new VertexAiProvider(
                $this->vertexProjectId,
                $this->vertexLocation,
                $this->aiModel,
                $jsonToTest
            );
            $result = $provider->testConnection();
        } else {
            $keyToTest = $this->aiApiKey;
            if ($keyToTest === '********') {
                $keyToTest = SystemSetting::getValue('ai_api_key');
            }

            if (empty($keyToTest)) {
                $this->connectionStatus = 'error';
                $this->connectionMessage = 'Chave de API não informada.';

                return;
            }

            $provider = new GeminiProvider($keyToTest, $this->aiModel);
            $result = $provider->testConnection();
        }

        if ($result['success']) {
            $this->connectionStatus = 'success';
            $this->connectionMessage = $result['message'];
        } else {
            $this->connectionStatus = 'error';
            $this->connectionMessage = $result['message'];
        }
    }

    public function save()
    {
        SystemSetting::setValue('ai_provider', $this->aiProvider);
        SystemSetting::setValue('ai_model', $this->aiModel);

        if ($this->aiProvider === 'gemini-studio') {
            if ($this->aiApiKey !== '********' && ! empty($this->aiApiKey)) {
                SystemSetting::setValue('ai_api_key', $this->aiApiKey);
            }
        } else {
            SystemSetting::setValue('vertex_project_id', $this->vertexProjectId);
            SystemSetting::setValue('vertex_location', $this->vertexLocation);
            if ($this->vertexServiceAccountJson !== '********' && ! empty($this->vertexServiceAccountJson)) {
                SystemSetting::setValue('vertex_service_account_json', $this->vertexServiceAccountJson);
            }
        }

        session()->flash('status', 'Configurações do Agente de IA salvas com sucesso!');
        $this->connectionStatus = '';
    }

    public function render()
    {
        return view('livewire.admin.configuracao-sistema');
    }
}
