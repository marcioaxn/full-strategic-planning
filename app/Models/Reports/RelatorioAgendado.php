<?php

namespace App\Models\Reports;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RelatorioAgendado extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'tab_relatorios_agendados';
    protected $primaryKey = 'cod_agendamento';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'dsc_tipo_relatorio',
        'dsc_frequencia',
        'txt_filtros',
        'dte_proxima_execucao',
        'bln_ativo',
    ];

    protected $casts = [
        'txt_filtros' => 'array',
        'dte_proxima_execucao' => 'datetime',
        'bln_ativo' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
