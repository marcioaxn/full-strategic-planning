<?php

namespace App\Models\StrategicPlanning;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class IntegracaoInstrumento extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'strategic_planning.tab_integracao_instrumentos';

    protected $primaryKey = 'cod_integracao';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_pei',
        'dsc_instrumento',
        'dsc_tipo_instrumento',
        'txt_pontos_atencao',
        'txt_tarefas',
        'dsc_intensidade',
        'num_ordem',
    ];

    protected $casts = [
        'num_ordem' => 'integer',
    ];

    // A Agenda 2030/ODS deixou de ser um "tipo de instrumento" genérico:
    // agora tem aba dedicada com vínculo estruturado (rel_pei_ods).
    public const TIPOS = ['PPA', 'LOA', 'Plano Setorial', 'Outro'];

    public const INTENSIDADES = ['Alta', 'Media', 'Baixa'];

    /**
     * Chave gravada (sem acento) => rótulo exibido.
     */
    public const ROTULOS_INTENSIDADE = ['Alta' => 'Alta', 'Media' => 'Média', 'Baixa' => 'Baixa'];

    /**
     * Leva o valor gravado à chave do vocabulário.
     *
     * Dado de carga e de demonstração foi gravado como "Média" (com acento),
     * enquanto a tela grava "Media". Sem normalizar, o registro acentuado não
     * passava na validação ao ser editado — a tela dizia "salvo" e nada
     * mudava — e a etiqueta caía na cor de "Baixa".
     */
    public static function normalizarIntensidade(?string $valor): string
    {
        $chave = str_replace(['é', 'É'], 'e', trim((string) $valor));
        $chave = ucfirst(strtolower($chave));

        return in_array($chave, self::INTENSIDADES, true) ? $chave : 'Media';
    }

    public static function rotuloIntensidade(?string $valor): string
    {
        return self::ROTULOS_INTENSIDADE[self::normalizarIntensidade($valor)];
    }

    public function pei(): BelongsTo
    {
        return $this->belongsTo(PEI::class, 'cod_pei', 'cod_pei');
    }
}
