<?php

namespace App\Models\PEI;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnaliseAmbiental extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'tab_analise_ambiental';
    protected $primaryKey = 'cod_analise';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'cod_pei',
        'cod_organizacao',
        'dsc_tipo_analise',
        'dsc_categoria',
        'dsc_item',
        'num_impacto',
        'txt_observacao',
        'num_ordem',
    ];

    protected $casts = [
        'num_impacto' => 'integer',
        'num_ordem' => 'integer',
    ];

    // Constantes para tipos de análise
    const TIPO_SWOT = 'SWOT';
    const TIPO_PESTEL = 'PESTEL';

    // Categorias SWOT
    const SWOT_FORCA = 'Força';
    const SWOT_FRAQUEZA = 'Fraqueza';
    const SWOT_OPORTUNIDADE = 'Oportunidade';
    const SWOT_AMEACA = 'Ameaça';

    // Categorias PESTEL
    const PESTEL_POLITICO = 'Político';
    const PESTEL_ECONOMICO = 'Econômico';
    const PESTEL_SOCIAL = 'Social';
    const PESTEL_TECNOLOGICO = 'Tecnológico';
    const PESTEL_AMBIENTAL = 'Ambiental';
    const PESTEL_LEGAL = 'Legal';

    public static function categoriasSWOT(): array
    {
        return [
            self::SWOT_FORCA => ['label' => 'Forças', 'icon' => 'shield-fill-check', 'color' => 'success'],
            self::SWOT_FRAQUEZA => ['label' => 'Fraquezas', 'icon' => 'shield-fill-x', 'color' => 'danger'],
            self::SWOT_OPORTUNIDADE => ['label' => 'Oportunidades', 'icon' => 'lightning-fill', 'color' => 'primary'],
            self::SWOT_AMEACA => ['label' => 'Ameaças', 'icon' => 'exclamation-triangle-fill', 'color' => 'warning'],
        ];
    }

    public static function categoriasPESTEL(): array
    {
        return [
            self::PESTEL_POLITICO => ['label' => 'Político', 'icon' => 'bank', 'color' => 'primary'],
            self::PESTEL_ECONOMICO => ['label' => 'Econômico', 'icon' => 'cash-coin', 'color' => 'success'],
            self::PESTEL_SOCIAL => ['label' => 'Social', 'icon' => 'people-fill', 'color' => 'info'],
            self::PESTEL_TECNOLOGICO => ['label' => 'Tecnológico', 'icon' => 'cpu-fill', 'color' => 'secondary'],
            self::PESTEL_AMBIENTAL => ['label' => 'Ambiental', 'icon' => 'tree-fill', 'color' => 'success'],
            self::PESTEL_LEGAL => ['label' => 'Legal', 'icon' => 'file-earmark-text-fill', 'color' => 'warning'],
        ];
    }

    // Relacionamentos
    public function pei(): BelongsTo
    {
        return $this->belongsTo(PEI::class, 'cod_pei', 'cod_pei');
    }

    public function organizacao(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'cod_organizacao', 'cod_organizacao');
    }

    // Scopes
    public function scopeSwot($query)
    {
        return $query->where('dsc_tipo_analise', self::TIPO_SWOT);
    }

    public function scopePestel($query)
    {
        return $query->where('dsc_tipo_analise', self::TIPO_PESTEL);
    }

    public function scopeCategoria($query, string $categoria)
    {
        return $query->where('dsc_categoria', $categoria);
    }

    public function scopeOrdenado($query)
    {
        return $query->orderBy('num_ordem')->orderBy('created_at');
    }
}
