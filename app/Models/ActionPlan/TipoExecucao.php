<?php

namespace App\Models\ActionPlan;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoExecucao extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Tabela do banco de dados
     */
    protected $table = 'tab_tipo_execucao';

    /**
     * Chave primária
     */
    protected $primaryKey = 'cod_tipo_execucao';

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
        'dsc_tipo_execucao',
    ];

    /**
     * Constantes de tipos pré-definidos
     */
    const ACAO = 'c00b9ebc-7014-4d37-97dc-7875e55fff1b';
    const INICIATIVA = 'ecef6a50-c010-4cda-afc3-cbda245b55b0';
    const PROJETO = '57518c30-3bc5-4305-a998-8ce8b11550ed';

    /**
     * Relacionamento: Planos de Ação deste tipo
     */
    public function planosAcao(): HasMany
    {
        return $this->hasMany(PlanoDeAcao::class, 'cod_tipo_execucao', 'cod_tipo_execucao');
    }

    /**
     * Métodos auxiliares
     */

    /**
     * Verifica se é tipo Ação
     */
    public function isAcao(): bool
    {
        return $this->cod_tipo_execucao === self::ACAO;
    }

    /**
     * Verifica se é tipo Iniciativa
     */
    public function isIniciativa(): bool
    {
        return $this->cod_tipo_execucao === self::INICIATIVA;
    }

    /**
     * Verifica se é tipo Projeto
     */
    public function isProjeto(): bool
    {
        return $this->cod_tipo_execucao === self::PROJETO;
    }
}
