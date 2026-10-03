<?php

namespace App\Models;

use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\StrategicPlanning\MissaoVisaoValores;
use App\Models\StrategicPlanning\Valor;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Tabela do banco de dados
     */
    protected $table = 'organization.tab_organizacoes';

    /**
     * Chave primária
     */
    protected $primaryKey = 'cod_organizacao';

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
        'sgl_organizacao',
        'nom_organizacao',
        'rel_cod_organizacao',
    ];

    /**
     * Relacionamento: Organização pai (hierarquia)
     */
    public function pai(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'rel_cod_organizacao', 'cod_organizacao');
    }

    /**
     * Relacionamento: Organizações filhas
     */
    public function filhas(): HasMany
    {
        return $this->hasMany(Organization::class, 'rel_cod_organizacao', 'cod_organizacao');
    }

    /**
     * Relacionamento: Usuários da organização
     */
    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'rel_users_tab_organizacoes',
            'cod_organizacao',
            'user_id',
            'cod_organizacao',
            'id'
        );
    }

    /**
     * Relacionamento: Iniciativas
     */
    public function planosAcao(): HasMany
    {
        return $this->hasMany(PlanoDeAcao::class, 'cod_organizacao', 'cod_organizacao');
    }

    /**
     * Relacionamento: Identidade Estratégica (Missão/Visão)
     */
    public function identidadeEstrategica(): HasMany
    {
        return $this->hasMany(MissaoVisaoValores::class, 'cod_organizacao', 'cod_organizacao');
    }

    /**
     * Relacionamento: Valores
     */
    public function valores(): HasMany
    {
        return $this->hasMany(Valor::class, 'cod_organizacao', 'cod_organizacao');
    }

    /**
     * Métodos auxiliares
     */

    /**
     * Obter IDs de toda a hierarquia (esta organização + descendentes recursivamente)
     * Otimizado para evitar múltiplas queries usando recursão direta.
     */
    public function getDescendantsAndSelfIds(): array
    {
        return self::descendentesEProprio($this->cod_organizacao);
    }

    /**
     * Mapa filho → pai de todas as organizações, numa consulta só.
     *
     * A árvore é pequena (dezenas de unidades) e é percorrida em memória com
     * guarda contra ciclo: uma árvore corrompida (A filha de B e B filha de A)
     * levava a recursão de consultas a estourar e derrubava o Dashboard.
     *
     * @return array<string, string|null>
     */
    private static function mapaDePais(): array
    {
        return self::query()->pluck('rel_cod_organizacao', 'cod_organizacao')->all();
    }

    /** @return array<int, string> */
    public static function descendentesEProprio(string $codOrganizacao): array
    {
        $filhosDe = [];
        foreach (self::mapaDePais() as $filho => $pai) {
            if ($pai !== null && $pai !== $filho) {
                $filhosDe[$pai][] = $filho;
            }
        }

        $ids = [];
        $fila = [$codOrganizacao];
        while ($fila) {
            $atual = array_shift($fila);
            if (isset($ids[$atual])) {
                continue;
            }
            $ids[$atual] = true;
            array_push($fila, ...($filhosDe[$atual] ?? []));
        }

        return array_keys($ids);
    }

    /** @return array<int, string> a própria organização e todas as superiores, até a raiz */
    public static function ascendentesEProprio(string $codOrganizacao): array
    {
        $pais = self::mapaDePais();
        $ids = [];
        $atual = $codOrganizacao;

        while ($atual !== null && ! isset($ids[$atual])) {
            $ids[$atual] = true;
            $pai = $pais[$atual] ?? null;
            $atual = $pai !== $atual ? $pai : null;
        }

        return array_keys($ids);
    }

    /**
     * Retorna a lista de organizações formatada para seletores, respeitando a hierarquia.
     */
    public static function getTreeForSelector(?string $excludeId = null, $parentId = null, $level = 0): array
    {
        $query = self::query();

        if ($parentId === null) {
            // Inicia pelas raízes
            $query->whereColumn('cod_organizacao', 'rel_cod_organizacao');
        } else {
            $query->where('rel_cod_organizacao', $parentId)
                ->where('cod_organizacao', '!=', $parentId);
        }

        if ($excludeId) {
            $query->where('cod_organizacao', '!=', $excludeId);
        }

        $results = [];
        foreach ($query->orderBy('nom_organizacao')->get() as $org) {
            $results[] = [
                'id' => $org->cod_organizacao,
                'label' => str_repeat('   ', $level).($level > 0 ? '↳ ' : '').$org->sgl_organizacao.' - '.$org->nom_organizacao,
                'level' => $level,
            ];

            // Busca filhos recursivamente
            $results = array_merge($results, self::getTreeForSelector($excludeId, $org->cod_organizacao, $level + 1));
        }

        return $results;
    }

    /**
     * Verifica se é organização raiz (auto-referenciada ou pai nulo)
     */
    public function isRaiz(): bool
    {
        return $this->rel_cod_organizacao === null || $this->cod_organizacao === $this->rel_cod_organizacao;
    }

    /**
     * Obter nível hierárquico (0 = raiz, 1 = filha direta, etc.)
     */
    public function getNivelHierarquico(int $nivel = 0): int
    {
        if ($this->isRaiz()) {
            return $nivel;
        }

        if ($this->pai) {
            return $this->pai->getNivelHierarquico($nivel + 1);
        }

        return $nivel;
    }

    /**
     * Scopes
     */

    /**
     * Scope: Apenas organizações raiz
     */
    public function scopeRaiz($query)
    {
        // Raiz é a auto-referenciada (o padrão da base) ou a sem superior —
        // o mesmo critério de isRaiz().
        return $query->where(fn ($q) => $q
            ->whereColumn('cod_organizacao', 'rel_cod_organizacao')
            ->orWhereNull('rel_cod_organizacao'));
    }

    /**
     * Scope: Organizações filhas de uma específica
     */
    public function scopeFilhasDe($query, string $codOrganizacaoPai)
    {
        return $query->where('rel_cod_organizacao', $codOrganizacaoPai)
            ->where('cod_organizacao', '!=', $codOrganizacaoPai);
    }
}
