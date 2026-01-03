<?php

namespace App\Models\RiskManagement;

use App\Models\StrategicPlanning\Objetivo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class RiscoObjetivo extends Model
{
    use HasUuids;

    protected $table = 'tab_risco_objetivo';
    protected $primaryKey = 'cod_risco_objetivo';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'cod_risco',
        'cod_objetivo',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // === RELACIONAMENTOS ===

    public function risco()
    {
        return $this->belongsTo(Risco::class, 'cod_risco', 'cod_risco');
    }

    public function objetivo()
    {
        return $this->belongsTo(Objetivo::class, 'cod_objetivo', 'cod_objetivo');
    }
}