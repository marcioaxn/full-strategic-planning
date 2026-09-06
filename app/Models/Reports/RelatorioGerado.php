<?php

namespace App\Models\Reports;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Route;

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

    /** Há arquivo guardado, ou este registro só serve para regerar? */
    public function temArquivoGuardado(): bool
    {
        return trim((string) $this->dsc_caminho_arquivo) !== '';
    }

    /**
     * O endereço para gerar este mesmo relatório de novo, com os mesmos filtros.
     *
     * Devolve null quando a rota não foi gravada (registros anteriores à
     * migration) ou quando ela não existe mais — melhor não oferecer o botão do
     * que oferecer um link quebrado.
     */
    public function urlParaRegerar(): ?string
    {
        if (! $this->dsc_rota || ! Route::has($this->dsc_rota)) {
            return null;
        }

        $filtros = collect($this->txt_filtros_aplicados ?? [])
            ->filter(fn ($v) => is_scalar($v) && $v !== '')
            ->all();

        return route($this->dsc_rota, $filtros);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
