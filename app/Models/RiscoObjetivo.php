<?php

namespace App\Models;

use App\Models\PEI\ObjetivoEstrategico;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class RiscoObjetivo extends Model
{
    use HasUuids;

    protected $table = 'pei.tab_risco_objetivo';
    protected $primaryKey = 'cod_risco_objetivo';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'cod_risco',
        'cod_objetivo_estrategico',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // === RELACIONAMENTOS ===

    public function risco()
    {
        return $this->belongsTo(Risco::class, 'cod_risco', 'cod_risco');
    }

    public function objetivoEstrategico()
    {
        return $this->belongsTo(ObjetivoEstrategico::class, 'cod_objetivo_estrategico', 'cod_objetivo_estrategico');
    }
}
