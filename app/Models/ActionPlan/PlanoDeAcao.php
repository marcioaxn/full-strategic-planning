<?php

namespace App\Models\ActionPlan;

use App\Models\Organization;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\PerformanceIndicators\Indicador;
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
    protected $table = 'pei.tab_plano_de_acao';

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
        'cod_objetivo',
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
     * Relacionamento: Objetivo
     */
    public function objetivo(): BelongsTo
    {
        return $this->belongsTo(Objetivo::class, 'cod_objetivo', 'cod_objetivo');
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
     * Retorna a definição da legenda de status para os Planos de Ação (Fonte Única da Verdade)
     */
    public static function getStatusLegend(): array
    {
        return [
            'nao_iniciado' => [
                'label' => 'Não Iniciado / Sem Aferição',
                'color' => '#475569',
                'class' => 'secondary'
            ],
            'em_andamento' => [
                'label' => 'Em Andamento / Atrasado',
                'color' => '#F3C72B',
                'class' => 'warning'
            ],
            'concluido' => [
                'label' => 'Todos os Planos Concluídos',
                'color' => '#429B22',
                'class' => 'success'
            ],
        ];
    }

    /**
     * Retorna a cor do Grau de Satisfação do Plano (Padrão do Projeto)
     * Cinza (secondary): Não Iniciado / Suspenso
     * Laranja (warning): Em Andamento / Atrasado
     * Verde (success): Concluído
     */
    public function getSatisfacaoColor(): string
    {
        return match($this->bln_status) {
            'Concluído'    => '#429B22', // success
            'Em Andamento' => '#F3C72B', // warning
            'Atrasado'     => '#F3C72B', // warning
            default        => '#475569', // secondary
        };
    }

    /**
     * Retorna a classe CSS para o texto (contraste)
     */
    public function getSatisfacaoTextClass(): string
    {
        return match($this->bln_status) {
            'Em Andamento' => 'text-dark',
            'Atrasado'     => 'text-dark',
            'Concluído'    => 'text-white',
            default        => 'text-white',
        };
    }

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