<?php

namespace App\Models\Reports;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RelatorioGerado extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'tab_relatorios_gerados';
    protected $primaryKey = 'cod_relatorio_gerado';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'dsc_tipo_relatorio',
        'dsc_caminho_arquivo',
        'dsc_formato',
        'txt_filtros_aplicados',
        'num_tamanho_bytes',
    ];

    protected $casts = [
        'txt_filtros_aplicados' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
