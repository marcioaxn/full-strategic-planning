<?php

namespace App\Models\PEI;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcessoAtividadeCadeiaValor extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Tabela do banco de dados
     */
    protected $table = 'tab_processos_atividade_cadeia_valor';

    /**
     * Chave primária
     */
    protected $primaryKey = 'cod_processo_atividade_cadeia_valor';

    /**
     * Tipo da chave primária
     */
    protected $keyType = 'string';

    /**
     * Chave primária não é auto-incremental
     */
    public $incrementing = false;

    /**
     * Atributos mass assignable
     */
    protected $fillable = [
        'cod_atividade_cadeia_valor',
        'dsc_entrada',
        'dsc_transformacao',
        'dsc_saida',
    ];

    /**
     * Relacionamento: Atividade da Cadeia de Valor
     */
    public function atividadeCadeiaValor(): BelongsTo
    {
        return $this->belongsTo(AtividadeCadeiaValor::class, 'cod_atividade_cadeia_valor', 'cod_atividade_cadeia_valor');
    }

    /**
     * Scopes
     */

    /**
     * Scope: Por atividade
     */
    public function scopePorAtividade($query, string $codAtividade)
    {
        return $query->where('cod_atividade_cadeia_valor', $codAtividade);
    }
}
