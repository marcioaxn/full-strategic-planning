<?php

namespace App\Models\StrategicPlanning;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AtividadeCadeiaValor extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Tabela do banco de dados
     */
    protected $table = 'strategic_planning.tab_atividade_cadeia_valor';

    /**
     * Chave primária
     */
    protected $primaryKey = 'cod_atividade_cadeia_valor';

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
        'cod_pei',
        'cod_perspectiva',
        'dsc_atividade',
        'dsc_tipo',
        'num_ordem',
    ];

    /**
     * Tipos de atividade da Cadeia de Valor — FONTE ÚNICA.
     *
     * Combo do formulário, regra de validação, agrupamento da tela e do PDF
     * derivam TODOS desta constante. Antes a lista estava repetida em cinco
     * pontos hardcoded: acrescentar um tipo fazia a opção aparecer no combo, o
     * cliente salvar, e a atividade sumir da tela — porque o render só montava
     * dois grupos e o terceiro não era lido por ninguém.
     *
     * Como o vocabulário vive no código e não no banco, um tipo novo chega a
     * TODO cliente no `git pull`: não há seeder a rodar.
     *
     * A ordem aqui é a ordem de exibição.
     */
    public const TIPOS = ['Finalística', 'Suporte', 'Valores públicos'];

    /**
     * Aparência e texto de apoio de cada grupo na tela e no relatório.
     *
     * @return array<string, array{cor: string, icone: string, titulo: string, ajuda: string}>
     */
    public static function apresentacaoDosTipos(): array
    {
        return [
            'Finalística' => [
                'cor' => 'primary',
                'icone' => 'bi-arrow-right-circle-fill',
                'titulo' => 'Atividades Finalísticas',
                'ajuda' => 'Produtos e serviços entregues diretamente à sociedade',
            ],
            'Suporte' => [
                'cor' => 'secondary',
                'icone' => 'bi-columns-gap',
                'titulo' => 'Atividades de Suporte',
                'ajuda' => 'Infraestrutura, pessoas, tecnologia e processos internos de apoio',
            ],
            'Valores públicos' => [
                'cor' => 'success',
                'icone' => 'bi-award',
                'titulo' => 'Valores Públicos',
                'ajuda' => 'O que a cadeia de valor entrega à sociedade como resultado',
            ],
        ];
    }

    protected $casts = [
        'num_ordem' => 'integer',
    ];

    /**
     * Relacionamento: PEI
     */
    public function pei(): BelongsTo
    {
        return $this->belongsTo(PEI::class, 'cod_pei', 'cod_pei');
    }

    /**
     * Relacionamento: Perspectiva BSC
     */
    public function perspectiva(): BelongsTo
    {
        return $this->belongsTo(Perspectiva::class, 'cod_perspectiva', 'cod_perspectiva');
    }

    /**
     * Relacionamento: Processos da Atividade
     */
    public function processos(): HasMany
    {
        return $this->hasMany(ProcessoAtividadeCadeiaValor::class, 'cod_atividade_cadeia_valor', 'cod_atividade_cadeia_valor');
    }

    /**
     * Scopes
     */

    /**
     * Scope: Por PEI
     */
    public function scopePorPei($query, string $codPei)
    {
        return $query->where('cod_pei', $codPei);
    }

    /**
     * Scope: Por perspectiva
     */
    public function scopePorPerspectiva($query, string $codPerspectiva)
    {
        return $query->where('cod_perspectiva', $codPerspectiva);
    }
}
