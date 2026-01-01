<?php

namespace App\Models;

use App\Models\PEI\Objetivo;
use App\Models\PEI\PEI;
use App\Models\PEI\RiscoMitigacao;
use App\Models\PEI\RiscoOcorrencia;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Risco extends Model implements Auditable
{
    use HasFactory, HasUuids, SoftDeletes, \OwenIt\Auditing\Auditable;

    protected $table = 'pei.tab_risco';
    protected $primaryKey = 'cod_risco';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'cod_pei',
        'cod_organizacao',
        'num_codigo_risco',
        'dsc_titulo',
        'txt_descricao',
        'dsc_categoria',
        'dsc_status',
        'num_probabilidade',
        'num_impacto',
        'num_nivel_risco',
        'txt_causas',
        'txt_consequencias',
        'cod_responsavel_monitoramento',
    ];

    protected $casts = [
        'num_codigo_risco' => 'integer',
        'num_probabilidade' => 'integer',
        'num_impacto' => 'integer',
        'num_nivel_risco' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // === RELACIONAMENTOS ===

    public function pei()
    {
        return $this->belongsTo(PEI::class, 'cod_pei', 'cod_pei');
    }

    public function organizacao()
    {
        return $this->belongsTo(Organization::class, 'cod_organizacao', 'cod_organizacao');
    }

    public function responsavel()
    {
        return $this->belongsTo(User::class, 'cod_responsavel_monitoramento', 'id');
    }

    public function objetivos()
    {
        return $this->belongsToMany(
            Objetivo::class,
            'pei.tab_risco_objetivo',
            'cod_risco',
            'cod_objetivo',
            'cod_risco',
            'cod_objetivo'
        )->withTimestamps();
    }

    public function mitigacoes()
    {
        return $this->hasMany(RiscoMitigacao::class, 'cod_risco', 'cod_risco');
    }

    public function ocorrencias()
    {
        return $this->hasMany(RiscoOcorrencia::class, 'cod_risco', 'cod_risco');
    }

    // === SCOPES ===

    public function scopeAtivos($query)
    {
        return $query->whereNotIn('dsc_status', ['Encerrado']);
    }

    public function scopeCriticos($query)
    {
        return $query->where('num_nivel_risco', '>=', 16);
    }

    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('dsc_categoria', $categoria);
    }

    public function scopePorNivel($query, $nivelMin, $nivelMax = null)
    {
        $q = $query->where('num_nivel_risco', '>=', $nivelMin);

        if ($nivelMax) {
            $q->where('num_nivel_risco', '<=', $nivelMax);
        }

        return $q;
    }

    // === MÉTODOS AUXILIARES ===

    public function calcularNivelRisco()
    {
        $this->num_nivel_risco = $this->num_probabilidade * $this->num_impacto;
        return $this->num_nivel_risco;
    }

    public function getNivelRiscoLabel()
    {
        $nivel = $this->num_nivel_risco;

        if ($nivel >= 16) return 'Crítico';
        if ($nivel >= 10) return 'Alto';
        if ($nivel >= 5) return 'Médio';
        return 'Baixo';
    }

    public function getNivelRiscoCor()
    {
        $nivel = $this->num_nivel_risco;

        if ($nivel >= 16) return '#dc2626'; // Vermelho
        if ($nivel >= 10) return '#f97316'; // Laranja
        if ($nivel >= 5) return '#eab308';  // Amarelo
        return '#65a30d'; // Verde
    }

    public function getNivelRiscoBadgeClass()
    {
        $nivel = $this->num_nivel_risco;

        if ($nivel >= 16) return 'bg-danger';
        if ($nivel >= 10) return 'bg-warning';
        if ($nivel >= 5) return 'bg-info';
        return 'bg-success';
    }

    public function isCritico()
    {
        return $this->num_nivel_risco >= 16;
    }

    public function temPlanoMitigacao()
    {
        return $this->mitigacoes()->count() > 0;
    }

    public function temOcorrencia()
    {
        return $this->ocorrencias()->count() > 0;
    }

    public function getProbabilidadeLabel()
    {
        return match($this->num_probabilidade) {
            1 => 'Muito Baixa',
            2 => 'Baixa',
            3 => 'Média',
            4 => 'Alta',
            5 => 'Muito Alta',
            default => 'Não definida'
        };
    }

    public function getImpactoLabel()
    {
        return match($this->num_impacto) {
            1 => 'Muito Baixo',
            2 => 'Baixo',
            3 => 'Médio',
            4 => 'Alto',
            5 => 'Muito Alto',
            default => 'Não definido'
        };
    }

    // === BOOT ===

    protected static function boot()
    {
        parent::boot();

        // Ao criar/atualizar, calcular nível de risco automaticamente
        static::saving(function ($risco) {
            if ($risco->num_probabilidade && $risco->num_impacto) {
                $risco->calcularNivelRisco();
            }

            // Auto-incrementar código do risco
            if (!$risco->num_codigo_risco) {
                $ultimoCodigo = static::where('cod_pei', $risco->cod_pei)
                    ->max('num_codigo_risco') ?? 0;
                $risco->num_codigo_risco = $ultimoCodigo + 1;
            }
        });
    }
}