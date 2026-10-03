<?php

namespace App\Models;

use App\Concerns\ResolveEscopoOrganizacional;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasUuids;
    use Notifiable;
    use ResolveEscopoOrganizacional;
    use TwoFactorAuthenticatable;

    /**
     * Tabela do banco de dados
     */
    protected $table = 'pei.users';

    /**
     * Chave primária
     */
    protected $primaryKey = 'id';

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
        'name',
        'email',
        'password',
        'ativo',
        'adm',
        'trocarsenha',
        'theme_color',
    ];

    /**
     * Atributos que devem ser hidden
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Atributos que devem ser cast
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'ativo' => 'boolean',
            'adm' => 'boolean',
            'trocarsenha' => 'integer',
        ];
    }

    /**
     * Relacionamento: Organizações que o usuário pertence
     */
    public function organizacoes(): BelongsToMany
    {
        return $this->belongsToMany(
            Organization::class,
            'rel_users_tab_organizacoes',
            'user_id',
            'cod_organizacao',
            'id',
            'cod_organizacao'
        )->wherePivotNull('deleted_at');
    }

    /**
     * Relacionamento: Perfis de acesso do usuário
     */
    public function perfisAcesso(): BelongsToMany
    {
        return $this->belongsToMany(
            PerfilAcesso::class,
            'rel_users_tab_organizacoes_tab_perfil_acesso',
            'user_id',
            'cod_perfil',
            'id',
            'cod_perfil'
        )->withPivot('cod_organizacao', 'cod_plano_de_acao')->wherePivotNull('deleted_at');
    }

    /**
     * Os perfis que valem PARA UMA organização.
     *
     * 🔴 Antes, a permissão juntava todos os perfis do usuário, em qualquer
     * unidade: quem era Administrador na unidade A e Gestor Substituto na B
     * tinha poderes de Administrador também na B. Agora cada vínculo vale
     * onde foi dado:
     *  - Administrador da Unidade e Consulta: na unidade do vínculo e nas
     *    subordinadas a ela;
     *  - Gestor Responsável e Substituto: só na unidade do vínculo.
     *
     * @return array<int, string> cod_perfil distintos
     */
    public function perfisEfetivosNaOrganizacao(string $codOrganizacao): array
    {
        if (! $this->relationLoaded('perfisAcesso')) {
            $this->load('perfisAcesso');
        }

        $acima = Organization::ascendentesEProprio($codOrganizacao);

        return $this->perfisAcesso
            ->filter(function (PerfilAcesso $perfil) use ($codOrganizacao, $acima) {
                $orgDoVinculo = $perfil->pivot->cod_organizacao;

                if ($orgDoVinculo === $codOrganizacao) {
                    return true;
                }

                return in_array($perfil->cod_perfil, PerfilAcesso::PERFIS_HIERARQUICOS, true)
                    && in_array($orgDoVinculo, $acima, true);
            })
            ->pluck('cod_perfil')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Pode alterar o que é da INSTITUIÇÃO inteira, não de uma unidade:
     * perspectivas, objetivos, faixas do farol, cadeia de valor, a abertura do
     * ciclo e o futuro almejado. Essas tabelas não têm organização — uma
     * alteração vale para todas as unidades. Por isso só o Super Administrador
     * ou o Administrador da unidade RAIZ.
     */
    public function podeEditarInstitucional(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (! $this->relationLoaded('perfisAcesso')) {
            $this->load('perfisAcesso');
        }

        $raizes = Organization::query()->raiz()->pluck('cod_organizacao')->all();

        return $this->perfisAcesso->contains(
            fn (PerfilAcesso $p) => $p->cod_perfil === PerfilAcesso::ADMIN_UNIDADE
                && in_array($p->pivot->cod_organizacao, $raizes, true)
        );
    }

    /** É Administrador nesta unidade — pelo vínculo nela ou numa superior. */
    public function ehAdministradorEm(?string $codOrganizacao): bool
    {
        if (! $codOrganizacao) {
            return false;
        }

        return in_array(PerfilAcesso::ADMIN_UNIDADE, $this->perfisEfetivosNaOrganizacao($codOrganizacao), true);
    }

    /** Responde por esta iniciativa, como Gestor Responsável ou Substituto. */
    public function ehGestorDaIniciativa(?string $codPlanoDeAcao): bool
    {
        return $codPlanoDeAcao
            && ($this->isGestorResponsavel($codPlanoDeAcao) || $this->isGestorSubstituto($codPlanoDeAcao));
    }

    /** Tem algum vínculo de perfil? Conta recém-criada pelo autocadastro não tem. */
    public function temPerfilDeAcesso(): bool
    {
        if (! $this->relationLoaded('perfisAcesso')) {
            $this->load('perfisAcesso');
        }

        return $this->perfisAcesso->isNotEmpty();
    }

    /**
     * Relacionamento: Ações (logs simples)
     */
    public function acoes(): HasMany
    {
        return $this->hasMany(Acao::class, 'user_id', 'id');
    }

    /**
     * Relacionamento: Auditorias
     */
    public function audits(): HasMany
    {
        return $this->hasMany(TabAudit::class, 'user_id', 'id');
    }

    /**
     * Métodos auxiliares
     */

    /**
     * Verifica se usuário é Super Administrador.
     *
     * A fonte de verdade é o PERFIL de acesso vinculado: o usuário é Super
     * Administrador quando possui o perfil PerfilAcesso::SUPER_ADMIN. O antigo
     * campo "adm" deixou de determinar isso (mantido apenas por compatibilidade,
     * sincronizado no cadastro).
     */
    public function isSuperAdmin(): bool
    {
        if (! $this->relationLoaded('perfisAcesso')) {
            $this->load('perfisAcesso');
        }

        return $this->perfisAcesso->contains('cod_perfil', PerfilAcesso::SUPER_ADMIN);
    }

    /**
     * Verifica se usuário está ativo
     */
    public function isAtivo(): bool
    {
        return $this->ativo === true;
    }

    /**
     * Verifica se usuário precisa trocar senha
     */
    public function deveTrocarSenha(): bool
    {
        return $this->trocarsenha === 1;
    }

    /**
     * Verifica se usuário tem permissão em uma organização
     */
    public function temPermissaoOrganizacao(Organization $org): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->organizacoes()->where('cod_organizacao', $org->cod_organizacao)->exists();
    }

    /**
     * Obter perfis do usuário em uma organização específica
     */
    public function perfisNaOrganizacao(Organization $org)
    {
        return $this->perfisAcesso()
            ->wherePivot('cod_organizacao', $org->cod_organizacao)
            ->get();
    }

    /**
     * Verifica se usuário é gestor responsável de um plano
     */
    public function isGestorResponsavel(string $codPlanoDeAcao): bool
    {
        return $this->perfisAcesso()
            ->where('tab_perfil_acesso.cod_perfil', PerfilAcesso::GESTOR_RESPONSAVEL)
            ->wherePivot('cod_plano_de_acao', $codPlanoDeAcao)
            ->exists();
    }

    /**
     * Verifica se usuário é gestor substituto de um plano
     */
    public function isGestorSubstituto(string $codPlanoDeAcao): bool
    {
        return $this->perfisAcesso()
            ->where('tab_perfil_acesso.cod_perfil', PerfilAcesso::GESTOR_SUBSTITUTO)
            ->wherePivot('cod_plano_de_acao', $codPlanoDeAcao)
            ->exists();
    }

    /**
     * Scopes
     */

    /**
     * Scope: Apenas usuários ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope: Apenas Super Administradores (pelo perfil vinculado).
     */
    public function scopeAdministradores($query)
    {
        return $query->whereHas('perfisAcesso', function ($q) {
            $q->where('tab_perfil_acesso.cod_perfil', PerfilAcesso::SUPER_ADMIN);
        });
    }

    /**
     * Scope: Usuários que devem trocar senha
     */
    public function scopeDevemTrocarSenha($query)
    {
        return $query->where('trocarsenha', 1);
    }
}
