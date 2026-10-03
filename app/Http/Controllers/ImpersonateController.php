<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ImpersonateController extends Controller
{
    /**
     * Inicia a impersonação de um usuário.
     * Apenas Administradores Gerais (adm=true) podem impersonar.
     */
    public function start(string $userId)
    {
        $admin = Auth::user();

        abort_unless($admin && $admin->isSuperAdmin(), 403, 'Apenas o Administrador Geral pode assumir a identidade de outro usuário.');

        // Já está impersonando? Bloqueia impersonação aninhada.
        abort_if(session()->has('impersonator_id'), 403, 'Encerre a impersonação atual antes de iniciar outra.');

        $alvo = User::findOrFail($userId);

        // Não pode impersonar a si mesmo
        if ($alvo->id === $admin->id) {
            return redirect()->route('admin.perfis')->with('error', 'Você não pode assumir a sua própria identidade.');
        }

        Log::warning('[IMPERSONATE] Início', [
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'alvo_id' => $alvo->id,
            'alvo_email' => $alvo->email,
            'timestamp' => now()->toIso8601String(),
        ]);

        session(['impersonator_id' => $admin->id]);

        $this->entrarComo($alvo);

        return redirect()->route('dashboard')->with('status', 'Você está agora visualizando o sistema como '.$alvo->name.'.');
    }

    /**
     * Encerra a impersonação e retorna ao Administrador Geral original.
     */
    public function stop()
    {
        $impersonatorId = session('impersonator_id');

        abort_unless($impersonatorId, 403, 'Nenhuma impersonação ativa.');

        $impersonado = Auth::user();
        $admin = User::findOrFail($impersonatorId);

        Log::warning('[IMPERSONATE] Fim', [
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'impersonado_id' => $impersonado?->id,
            'impersonado_email' => $impersonado?->email,
            'timestamp' => now()->toIso8601String(),
        ]);

        session()->forget('impersonator_id');

        $this->entrarComo($admin);

        return redirect()->route('admin.perfis')->with('status', 'Impersonação encerrada. Você voltou à sua identidade.');
    }

    /**
     * Troca o usuário logado sem derrubar a sessão.
     *
     * O guard sanctum guarda em cache o usuário anterior. Sem esquecê-lo, o
     * AuthenticateSession (jetstream.auth_session) grava, ao fim desta
     * requisição, o hash da senha do usuário ANTERIOR; na seguinte, o hash não
     * bate com o do novo usuário e o middleware desloga — a tela caía no portal
     * público. Teste: ImpersonacaoMantemSessaoTest.
     */
    private function entrarComo(User $user): void
    {
        Auth::guard('web')->login($user);
        Auth::forgetGuards();
    }
}
