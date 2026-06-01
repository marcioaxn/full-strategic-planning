<?php

namespace App\Models\Agenda2030;

use App\Models\StrategicPlanning\Objetivo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * ODS — Objetivo de Desenvolvimento Sustentável (Agenda 2030 / ONU).
 *
 * Tabela de referência com os 17 ODS oficiais. Não usa UUID nem soft delete:
 * a chave primária é o próprio número do ODS (1 a 17), imutável e universal.
 */
class ODS extends Model
{
    protected $table = 'strategic_planning.tab_ods';

    protected $primaryKey = 'num_ods';
    protected $keyType = 'integer';
    public $incrementing = false;

    protected $fillable = [
        'num_ods',
        'nom_ods',
        'nom_ods_abreviado',
        'dsc_ods',
        'cod_cor',
        'nom_icone',
    ];

    protected $casts = [
        'num_ods' => 'integer',
    ];

    /**
     * Objetivos estratégicos vinculados a este ODS.
     */
    public function objetivos(): BelongsToMany
    {
        return $this->belongsToMany(
            Objetivo::class,
            'strategic_planning.rel_objetivo_ods',
            'num_ods',
            'cod_objetivo',
            'num_ods',
            'cod_objetivo'
        )->withPivot('txt_contribuicao')->withTimestamps();
    }

    /**
     * Número formatado com zero à esquerda (ex.: "01", "17").
     */
    public function getNumeroFormatadoAttribute(): string
    {
        return str_pad((string) $this->num_ods, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Caminho público do ícone IPEA. Retorna null se o arquivo não existir,
     * permitindo ao componente x-ods-badge cair no fallback de badge colorido.
     */
    public function getCaminhoIconeAttribute(): ?string
    {
        $arquivo = 'img/ods/ods-' . $this->numero_formatado . '.png';

        return file_exists(public_path($arquivo))
            ? asset($arquivo)
            : null;
    }

    /**
     * Scope: ordenar pelo número do ODS.
     */
    public function scopeOrdenado($query)
    {
        return $query->orderBy('num_ods');
    }
}
