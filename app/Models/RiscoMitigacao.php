<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class RiscoMitigacao extends Model implements Auditable
{
    use HasFactory, HasUuids, \OwenIt\Auditing\Auditable;

    protected $table = 'tab_risco_mitigacao';
    protected $primaryKey = 'cod_mitigacao';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'cod_risco',
        'dsc_tipo',
        'txt_descricao',
        'cod_responsavel',
        'dte_prazo',
        'dsc_status',
        'vlr_custo_estimado',
    ];

    protected $casts = [
        'dte_prazo' => 'date',
        'vlr_custo_estimado' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // === RELACIONAMENTOS ===

    public function risco()
    {
        return $this->belongsTo(Risco::class, 'cod_risco', 'cod_risco');
    }

    public function responsavel()
    {
        return $this->belongsTo(User::class, 'cod_responsavel', 'id');
    }

    // === SCOPES ===

    public function scopeAtrasados($query)
    {
        return $query->where('dte_prazo', '<', now())
                     ->where('dsc_status', '!=', 'Concluído');
    }

    public function scopePorStatus($query, $status)
    {
        return $query->where('dsc_status', $status);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('dsc_tipo', $tipo);
    }

    // === MÉTODOS AUXILIARES ===

    public function isAtrasado()
    {
        return $this->dte_prazo < now() && $this->dsc_status !== 'Concluído';
    }

    public function isConcluido()
    {
        return $this->dsc_status === 'Concluído';
    }

    public function getDiasRestantes()
    {
        if ($this->isConcluido()) {
            return 0;
        }

        return now()->diffInDays($this->dte_prazo, false);
    }

    public function getStatusBadgeClass()
    {
        return match($this->dsc_status) {
            'Concluído' => 'bg-success',
            'Em Andamento' => 'bg-primary',
            'A Fazer' => 'bg-secondary',
            default => 'bg-light'
        };
    }
}
