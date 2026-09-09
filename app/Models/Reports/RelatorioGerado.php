<?php

namespace App\Models\Reports;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Routing\Exceptions\UrlGenerationException;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RelatorioGerado extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'pei.tab_relatorios_gerados';

    protected $primaryKey = 'cod_relatorio_gerado';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'dsc_tipo_relatorio',
        'dsc_rota',
        'dsc_caminho_arquivo',
        'dsc_formato',
        'txt_filtros_aplicados',
        'num_tamanho_bytes',
    ];

    protected $casts = [
        'txt_filtros_aplicados' => 'array',
    ];

    /**
     * Guarda o arquivo gerado e registra a geração.
     *
     * 🔴 POR QUE ISTO EXISTE
     * "Gerados Recentemente" e o Histórico existem para o cliente reaver o
     * relatório que ele já gerou — o que ele apresentou numa reunião, com os
     * números daquele dia. Só que a geração pela tela mandava o PDF direto para
     * o navegador e gravava o registro com o caminho vazio: não havia arquivo
     * nenhum para reaver. Clicar em baixar respondia "Caminho de arquivo
     * inválido.".
     *
     * Regerar não substitui: sai um documento novo, com os dados de hoje. Quem
     * precisa do relatório que foi apresentado precisa daquele arquivo, não de
     * um parecido.
     *
     * O arquivo vai para o disco PRIVADO `relatorios` — nunca para public/ —,
     * e só sai de lá pela rota de download, que verifica a autorização.
     *
     * @param  array{content?: string, filename?: string}  $resultado
     */
    public static function registrar(
        array $resultado,
        string $tipo,
        ?string $userId,
        array $filtros = [],
        ?string $rota = null,
        ?string $formato = null,
    ): self {
        $nomeOriginal = $resultado['filename'] ?? 'relatorio.pdf';
        $conteudo = $resultado['content'] ?? '';
        $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION)) ?: 'pdf';

        // Mesma convenção de caminho que o agendador já usa, para os arquivos
        // dos dois caminhos de geração conviverem no mesmo disco.
        $caminho = 'relatorios/'.date('Y/m').'/'
            .Str::slug(pathinfo($nomeOriginal, PATHINFO_FILENAME)).'_'.Str::uuid().'.'.$extensao;

        Storage::disk('relatorios')->put($caminho, $conteudo);

        return static::create([
            'user_id' => $userId,
            'dsc_tipo_relatorio' => $tipo,
            'dsc_rota' => $rota,
            'dsc_caminho_arquivo' => $caminho,
            'dsc_formato' => $formato ?? ($extensao === 'pdf' ? 'pdf' : $extensao),
            'txt_filtros_aplicados' => $filtros,
            'num_tamanho_bytes' => strlen($conteudo),
        ]);
    }

    /** Há arquivo guardado, ou este registro só serve para regerar? */
    public function temArquivoGuardado(): bool
    {
        return trim((string) $this->dsc_caminho_arquivo) !== '';
    }

    /**
     * O endereço para gerar este mesmo relatório de novo, com os mesmos filtros.
     *
     * Devolve null quando a rota não foi gravada (registros anteriores à
     * migration), quando ela não existe mais, ou quando os filtros guardados não
     * bastam para montar o endereço — melhor não oferecer o botão do que
     * oferecer um link quebrado.
     *
     * O último caso é real: `relatorios.identidade` tem `{organizacaoId}`
     * obrigatório no caminho, e só a query string é gravada como filtro. Sem a
     * guarda, montar o link lançaria UrlGenerationException e derrubaria a tela
     * inteira — a listagem, não apenas o botão.
     */
    public function urlParaRegerar(): ?string
    {
        if (! $this->dsc_rota || ! Route::has($this->dsc_rota)) {
            return null;
        }

        $filtros = collect($this->txt_filtros_aplicados ?? [])
            ->filter(fn ($v) => is_scalar($v) && $v !== '')
            ->all();

        try {
            return route($this->dsc_rota, $filtros);
        } catch (UrlGenerationException) {
            return null;
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
