<?php

namespace App\Models\RiskManagement;

use App\Models\Organization;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Risco extends Model implements Auditable
{
    use HasFactory, HasUuids, \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $table = 'risk_management.tab_risco';

    protected $primaryKey = 'cod_risco';

    protected $keyType = 'string';

    public $incrementing = false;

    public const ESTRATEGIAS_RESPOSTA = ['Mitigar', 'Evitar', 'Transferir', 'Aceitar'];

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
        'dsc_estrategia_resposta',
        'txt_justificativa_estrategia',
        'dte_proxima_revisao',
    ];

    protected $casts = [
        'num_codigo_risco' => 'integer',
        'num_probabilidade' => 'integer',
        'num_impacto' => 'integer',
        'num_nivel_risco' => 'integer',
        'dte_proxima_revisao' => 'date',
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
            'tab_risco_objetivo',
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

    /**
     * O recorte de unidade da gestão de riscos: a unidade selecionada e as
     * subordinadas, dentro do que o usuário alcança; sem unidade, o escopo
     * inteiro do usuário. A lista e a matriz usam este mesmo recorte — a
     * matriz filtrava só a unidade exata e mostrava menos riscos que a lista.
     */
    public function scopeNoRecorteDaUnidade($query, ?string $codOrganizacao, User $usuario)
    {
        if (! $codOrganizacao) {
            return $usuario->aplicarEscopoOrganizacional($query);
        }

        $orgIds = Organization::descendentesEProprio($codOrganizacao);
        if (! $usuario->isSuperAdmin()) {
            $orgIds = array_values(array_intersect($orgIds, $usuario->organizacaoIdsPermitidas()->all()));
        }

        return $query->whereIn('risk_management.tab_risco.cod_organizacao', $orgIds);
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

    /**
     * A régua do nível de risco (P × I), num lugar só: lista, pré-visualização
     * do formulário, matriz, Excel e telas leem daqui. O Excel usava ≥ 15 para
     * Crítico e a pré-visualização cortava em 8 — o mesmo risco tinha duas cores.
     */
    public const NIVEL_CRITICO = 16;

    public const NIVEL_ALTO = 10;

    public const NIVEL_MEDIO = 5;

    public static function rotuloDoNivel(?int $nivel): string
    {
        $nivel = (int) $nivel;

        return match (true) {
            $nivel >= self::NIVEL_CRITICO => 'Crítico',
            $nivel >= self::NIVEL_ALTO => 'Alto',
            $nivel >= self::NIVEL_MEDIO => 'Médio',
            default => 'Baixo',
        };
    }

    public static function corDoNivel(?int $nivel): string
    {
        $nivel = (int) $nivel;

        return match (true) {
            $nivel >= self::NIVEL_CRITICO => '#dc2626', // Vermelho
            $nivel >= self::NIVEL_ALTO => '#f97316',    // Laranja
            $nivel >= self::NIVEL_MEDIO => '#eab308',   // Amarelo
            default => '#65a30d',                       // Verde
        };
    }

    public function getNivelRiscoLabel()
    {
        return self::rotuloDoNivel($this->num_nivel_risco);
    }

    public function getNivelRiscoCor()
    {
        return self::corDoNivel($this->num_nivel_risco);
    }

    /**
     * Estilo do selo do nível: a MESMA cor da matriz e da pré-visualização
     * (corDoNivel). As classes Bootstrap antigas pintavam Médio de azul e Alto
     * de amarelo — o mesmo risco tinha duas cores conforme a tela.
     * No amarelo (Médio) o texto é escuro, por contraste.
     */
    public function estiloDoSeloDeNivel(): string
    {
        $nivel = (int) $this->num_nivel_risco;
        $texto = $nivel >= self::NIVEL_MEDIO && $nivel < self::NIVEL_ALTO ? '#1f2937' : '#ffffff';

        return 'background-color: '.self::corDoNivel($nivel).'; color: '.$texto.';';
    }

    public function isCritico()
    {
        return $this->num_nivel_risco >= 16;
    }

    public function revisaoVencida(): bool
    {
        return $this->dsc_status !== 'Encerrado'
            && $this->dte_proxima_revisao !== null
            && $this->dte_proxima_revisao->isPast();
    }

    public function precisaJustificativa(): bool
    {
        return $this->dsc_estrategia_resposta === 'Aceitar';
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
        return match ($this->num_probabilidade) {
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
        return match ($this->num_impacto) {
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
            if (! $risco->num_codigo_risco) {
                // withTrashed: sem ele, excluir o último risco devolvia o número
                // ao próximo cadastro, e dois riscos diferentes viravam "R-07".
                $ultimoCodigo = static::withTrashed()->where('cod_pei', $risco->cod_pei)
                    ->max('num_codigo_risco') ?? 0;
                $risco->num_codigo_risco = $ultimoCodigo + 1;
            }
        });
    }
}
