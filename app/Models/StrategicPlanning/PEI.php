<?php

namespace App\Models\StrategicPlanning;

use App\Models\Agenda2030\ODS;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PEI extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Tabelas que pertencem ao ciclo pela coluna cod_pei (e têm exclusão lógica).
     * Documentos do acervo NÃO entram: o documento existe por si e só cita o ciclo.
     *
     * @var list<string>
     */
    public const TABELAS_DO_CICLO = [
        'strategic_planning.tab_perspectiva',
        'strategic_planning.tab_missao_visao_valores',
        'strategic_planning.tab_valores',
        'strategic_planning.tab_tema_norteador',
        'strategic_planning.tab_grau_satisfacao',
        'strategic_planning.tab_analise_ambiental',
        'strategic_planning.tab_estrategia_tows',
        'strategic_planning.tab_partes_interessadas',
        'strategic_planning.tab_cenarios_prospectivos',
        'strategic_planning.tab_atividade_cadeia_valor',
        'strategic_planning.tab_inaugurar_pei',
        'strategic_planning.tab_integracao_instrumentos',
        'strategic_planning.tab_calendario_eventos_pei',
        'strategic_planning.tab_rae',
        'risk_management.tab_risco',
    ];

    /**
     * Excluir o ciclo exclui (logicamente) tudo o que é dele.
     *
     * 🔴 Antes só o ciclo era marcado como excluído: identidade, perspectivas,
     * objetivos, iniciativas, indicadores, riscos e análises continuavam vivos e
     * apareciam em consultas que não filtram pelo ciclo (achado no teste pelo
     * navegador de 03/10/2026, com três ciclos de teste excluídos mais cedo).
     */
    protected static function booted(): void
    {
        static::deleting(function (PEI $pei) {
            if ($pei->isForceDeleting()) {
                return;
            }

            $agora = now();
            $marcar = fn (string $tabela, string $coluna, $valores) => DB::table($tabela)
                ->whereIn($coluna, collect($valores)->all())
                ->whereNull('deleted_at')
                ->update(['deleted_at' => $agora, 'updated_at' => $agora]);

            $objetivos = DB::table('strategic_planning.tab_objetivo')
                ->whereIn('cod_perspectiva', DB::table('strategic_planning.tab_perspectiva')->where('cod_pei', $pei->cod_pei)->select('cod_perspectiva'))
                ->pluck('cod_objetivo');

            // Iniciativas e indicadores pelo modelo: a exclusão deles já leva
            // entregas, indicadores da iniciativa e vínculos de Gestor.
            \App\Models\ActionPlan\PlanoDeAcao::whereIn('cod_objetivo', $objetivos)->get()->each->delete();
            \App\Models\PerformanceIndicators\Indicador::whereIn('cod_objetivo', $objetivos)->get()->each->delete();

            $marcar('strategic_planning.tab_futuro_almejado_objetivo', 'cod_objetivo', $objetivos);
            $marcar('strategic_planning.tab_objetivo_comentarios', 'cod_objetivo', $objetivos);
            $marcar('strategic_planning.tab_objetivo', 'cod_objetivo', $objetivos);

            $marcar('strategic_planning.tab_processos_atividade_cadeia_valor', 'cod_atividade_cadeia_valor',
                DB::table('strategic_planning.tab_atividade_cadeia_valor')->where('cod_pei', $pei->cod_pei)->pluck('cod_atividade_cadeia_valor'));
            $marcar('strategic_planning.tab_rae_encaminhamento', 'cod_rae',
                DB::table('strategic_planning.tab_rae')->where('cod_pei', $pei->cod_pei)->pluck('cod_rae'));

            $riscos = DB::table('risk_management.tab_risco')->where('cod_pei', $pei->cod_pei)->pluck('cod_risco');
            $marcar('risk_management.tab_risco_mitigacao', 'cod_risco', $riscos);
            $marcar('risk_management.tab_risco_ocorrencia', 'cod_risco', $riscos);

            foreach (self::TABELAS_DO_CICLO as $tabela) {
                $marcar($tabela, 'cod_pei', [$pei->cod_pei]);
            }
        });
    }

    /**
     * Tabela do banco de dados
     */
    protected $table = 'strategic_planning.tab_pei';

    /**
     * Chave primária
     */
    protected $primaryKey = 'cod_pei';

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
        'dsc_pei',
        'num_ano_inicio_pei',
        'num_ano_fim_pei',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'num_ano_inicio_pei' => 'integer',
        'num_ano_fim_pei' => 'integer',
    ];

    /**
     * Relacionamento: Perspectivas BSC
     */
    public function perspectivas(): HasMany
    {
        return $this->hasMany(Perspectiva::class, 'cod_pei', 'cod_pei');
    }

    /**
     * Relacionamento: Identidade Estratégica
     */
    public function identidadeEstrategica(): HasMany
    {
        return $this->hasMany(IdentidadeEstrategica::class, 'cod_pei', 'cod_pei');
    }

    /**
     * Relacionamento: Valores
     */
    public function valores(): HasMany
    {
        return $this->hasMany(Valor::class, 'cod_pei', 'cod_pei');
    }

    /**
     * Relacionamento: Atividades da Cadeia de Valor
     */
    public function atividadesCadeiaValor(): HasMany
    {
        return $this->hasMany(AtividadeCadeiaValor::class, 'cod_pei', 'cod_pei');
    }

    /**
     * Relacionamento: ODS aos quais o PEI adere institucionalmente (Agenda 2030).
     */
    public function ods(): BelongsToMany
    {
        return $this->belongsToMany(
            ODS::class,
            'strategic_planning.rel_pei_ods',
            'cod_pei',
            'num_ods',
            'cod_pei',
            'num_ods'
        )->withPivot('txt_contribuicao', 'dsc_intensidade')->withTimestamps()
            ->orderBy('strategic_planning.tab_ods.num_ods');
    }

    /**
     * O ciclo em que o usuário está trabalhando: o selecionado no topo da tela
     * ou, sem seleção (comando agendado, primeira visita), o ciclo vigente.
     *
     * Os relatórios usavam sempre o vigente: com outro ciclo selecionado, a
     * tela mostrava um ciclo e o PDF saía de outro.
     */
    public static function doContexto(): ?self
    {
        $id = session('pei_selecionado_id');

        return ($id ? static::find($id) : null) ?? static::ativos()->first();
    }

    /**
     * Métodos auxiliares
     */

    /**
     * Verifica se PEI está ativo (ano atual entre início e fim)
     */
    public function isAtivo(): bool
    {
        $anoAtual = now()->year;

        return $anoAtual >= $this->num_ano_inicio_pei && $anoAtual <= $this->num_ano_fim_pei;
    }

    /**
     * Scopes
     */

    /**
     * Scope: Apenas PEIs ativos
     */
    public function scopeAtivos($query)
    {
        $anoAtual = now()->year;

        // Ordem estável: com dois ciclos vigentes (ex.: um "Salvar como" no
        // mesmo período), o padrão de quem chega sem seleção — visitante da
        // transparência, primeiro acesso, relatório agendado — é sempre o
        // mesmo: o mais recente por início e, no empate, o mais antigo
        // cadastrado (o original, não a cópia).
        return $query->where('num_ano_inicio_pei', '<=', $anoAtual)
            ->where('num_ano_fim_pei', '>=', $anoAtual)
            ->orderBy('num_ano_inicio_pei', 'desc')
            ->orderBy('created_at')
            ->orderBy('cod_pei');
    }

    /**
     * Scope: PEIs futuros
     */
    public function scopeFuturos($query)
    {
        $anoAtual = now()->year;

        return $query->where('num_ano_inicio_pei', '>', $anoAtual);
    }

    /**
     * Scope: PEIs passados
     */
    public function scopePassados($query)
    {
        $anoAtual = now()->year;

        return $query->where('num_ano_fim_pei', '<', $anoAtual);
    }
}
