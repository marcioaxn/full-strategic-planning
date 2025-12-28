<?php

namespace App\Models\PEI;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class PlanoDeAcao extends Model implements Auditable
{
    use HasFactory, HasUuids, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    /**
     * Tabela do banco de dados
     */
    protected $table = 'tab_plano_de_acao';

    /**
     * Chave primária
     */
    protected $primaryKey = 'cod_plano_de_acao';

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
        'cod_objetivo_estrategico',
        'cod_tipo_execucao',
        'cod_organizacao',
        'num_nivel_hierarquico_apresentacao',
        'dsc_plano_de_acao',
        'dte_inicio',
        'dte_fim',
        'vlr_orcamento_previsto',
        'bln_status',
        'cod_ppa',
        'cod_loa',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'dte_inicio' => 'date',
        'dte_fim' => 'date',
        'vlr_orcamento_previsto' => 'decimal:2',
        'num_nivel_hierarquico_apresentacao' => 'integer',
    ];

    /**
     * Relacionamento: Objetivo Estratégico
     */
    public function objetivoEstrategico(): BelongsTo
    {
        return $this->belongsTo(ObjetivoEstrategico::class, 'cod_objetivo_estrategico', 'cod_objetivo_estrategico');
    }

    /**
     * Relacionamento: Tipo de Execução (Ação/Iniciativa/Projeto)
     */
    public function tipoExecucao(): BelongsTo
    {
        return $this->belongsTo(TipoExecucao::class, 'cod_tipo_execucao', 'cod_tipo_execucao');
    }

    /**
     * Relacionamento: Organização
     */
    public function organizacao(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'cod_organizacao', 'cod_organizacao');
    }

    /**
     * Relacionamento: Entregas
     */
    public function entregas(): HasMany
    {
        return $this->hasMany(Entrega::class, 'cod_plano_de_acao', 'cod_plano_de_acao');
    }

    /**
     * Relacionamento: Indicadores
     */
    public function indicadores(): HasMany
    {
        return $this->hasMany(Indicador::class, 'cod_plano_de_acao', 'cod_plano_de_acao');
    }

    /**
     * Métodos auxiliares
     */

    /**
     * Verifica se plano está atrasado
     */
    public function isAtrasado(): bool
    {
        return now()->greaterThan($this->dte_fim) && $this->bln_status !== 'Concluído';
    }

    /**
     * Calcula percentual de progresso (baseado em entregas)
     */
    public function calcularProgressoEntregas(): float
    {
        $totalEntregas = $this->entregas()->count();
        if ($totalEntregas === 0) {
            return 0;
        }

        $entregasConcluidas = $this->entregas()->where('bln_status', 'Concluído')->count();
        return ($entregasConcluidas / $totalEntregas) * 100;
    }

    /**
     * Scopes
     */

    /**
     * Scope: Por tipo de execução
     */
    public function scopePorTipo($query, string $tipo)
    {
        return $query->whereHas('tipoExecucao', function($q) use ($tipo) {
            $q->where('dsc_tipo_execucao', $tipo);
        });
    }

    /**
     * Scope: Por status
     */
    public function scopePorStatus($query, string $status)
    {
        return $query->where('bln_status', $status);
    }

    /**
     * Scope: Planos atrasados
     */
    public function scopeAtrasados($query)
    {
        return $query->where('dte_fim', '<', now())
                     ->where('bln_status', '!=', 'Concluído');
    }

    /**
     * Scope: Planos em andamento
     */
    public function scopeEmAndamento($query)
    {
        return $query->where('dte_inicio', '<=', now())
                     ->where('dte_fim', '>=', now())
                     ->where('bln_status', '!=', 'Concluído');
    }
}
