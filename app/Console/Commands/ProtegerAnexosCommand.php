<?php

namespace App\Console\Commands;

use App\Models\ActionPlan\EntregaAnexo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Move os anexos de entrega e as evidências de evolução que ainda estão no
 * disco PÚBLICO (storage/app/public, servido em /storage) para o disco
 * privado (storage/app/private), mantendo o mesmo caminho relativo.
 *
 * Auditoria de segurança (IDR-03): no disco público o arquivo era baixado por
 * URL direta, sem login. O código novo já grava no privado e lê dos dois; este
 * comando fecha a porta dos arquivos antigos.
 *
 * Idempotente: arquivo já movido não é tocado. Nada é apagado sem antes ter
 * sido gravado no destino.
 */
class ProtegerAnexosCommand extends Command
{
    protected $signature = 'entregas:proteger-anexos {--simular : Só lista o que seria movido}';

    protected $description = 'Move anexos de entrega e evidências de evolução do disco público para o privado';

    /** @var list<string> */
    private const PASTAS = [EntregaAnexo::PASTA, 'pei/evidencias'];

    public function handle(): int
    {
        $publico = Storage::disk('public');
        $privado = Storage::disk(EntregaAnexo::DISCO);
        $movidos = 0;

        foreach (self::PASTAS as $pasta) {
            foreach ($publico->allFiles($pasta) as $arquivo) {
                if ($this->option('simular')) {
                    $this->line("  moveria: {$arquivo}");
                    $movidos++;

                    continue;
                }

                if (! $privado->exists($arquivo)) {
                    $privado->writeStream($arquivo, $publico->readStream($arquivo));
                }

                if ($privado->exists($arquivo) && $privado->size($arquivo) === $publico->size($arquivo)) {
                    $publico->delete($arquivo);
                    $movidos++;
                } else {
                    $this->error("  não foi possível confirmar a cópia de {$arquivo}; o original foi mantido.");
                }
            }
        }

        $this->info(($this->option('simular') ? 'Seriam movidos: ' : 'Movidos para o disco privado: ').$movidos.' arquivo(s).');

        return self::SUCCESS;
    }
}
