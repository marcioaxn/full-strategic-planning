<?php

namespace App\Models;

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
    use TwoFactorAuthenticatable;

    /**
     * Tabela do banco de dados
     */
    protected $table = 'users';

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
        );
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
        )->withPivot('cod_organizacao', 'cod_plano_de_acao');
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
     * Verifica se usuário é Super Administrador
     */
    public function isSuperAdmin(): bool
    {
        return $this->adm === true;
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
            ->where('cod_perfil', PerfilAcesso::GESTOR_RESPONSAVEL)
            ->wherePivot('cod_plano_de_acao', $codPlanoDeAcao)
            ->exists();
    }

    /**
     * Verifica se usuário é gestor substituto de um plano
     */
    public function isGestorSubstituto(string $codPlanoDeAcao): bool
    {
        return $this->perfisAcesso()
            ->where('cod_perfil', PerfilAcesso::GESTOR_SUBSTITUTO)
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
     * Scope: Apenas administradores
     */
    public function scopeAdministradores($query)
    {
        return $query->where('adm', true);
    }

    /**
     * Scope: Usuários que devem trocar senha
     */
    public function scopeDevemTrocarSenha($query)
    {
        return $query->where('trocarsenha', 1);
    }
}
