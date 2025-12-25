<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class RiscoOcorrencia extends Model implements Auditable
{
    use HasFactory, HasUuids, \OwenIt\Auditing\Auditable;

    protected $table = 'pei.tab_risco_ocorrencia';
    protected $primaryKey = 'cod_ocorrencia';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'cod_risco',
        'dte_ocorrencia',
        'txt_descricao',
        'num_impacto_real',
        'txt_acoes_tomadas',
        'txt_licoes_aprendidas',
    ];

    protected $casts = [
        'dte_ocorrencia' => 'date',
        'num_impacto_real' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // === RELACIONAMENTOS ===

    public function risco()
    {
        return $this->belongsTo(Risco::class, 'cod_risco', 'cod_risco');
    }

    // === SCOPES ===

    public function scopeRecentes($query, $dias = 30)
    {
        return $query->where('dte_ocorrencia', '>=', now()->subDays($dias));
    }

    public function scopePorPeriodo($query, $dataInicio, $dataFim)
    {
        return $query->whereBetween('dte_ocorrencia', [$dataInicio, $dataFim]);
    }

    // === MÉTODOS AUXILIARES ===

    public function getImpactoRealLabel()
    {
        return match($this->num_impacto_real) {
            1 => 'Muito Baixo',
            2 => 'Baixo',
            3 => 'Médio',
            4 => 'Alto',
            5 => 'Muito Alto',
            default => 'Não avaliado'
        };
    }

    public function getImpactoRealCor()
    {
        return match($this->num_impacto_real) {
            5 => '#dc2626', // Vermelho
            4 => '#f97316', // Laranja
            3 => '#eab308', // Amarelo
            2 => '#65a30d', // Verde claro
            1 => '#22c55e', // Verde
            default => '#94a3b8' // Cinza
        };
    }

    public function isRecente($dias = 7)
    {
        return $this->dte_ocorrencia >= now()->subDays($dias);
    }
}
