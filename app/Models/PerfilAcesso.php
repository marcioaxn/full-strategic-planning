<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PerfilAcesso extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Tabela do banco de dados
     */
    protected $table = 'tab_perfil_acesso';

    /**
     * Chave primária
     */
    protected $primaryKey = 'cod_perfil';

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
        'dsc_perfil',
        'dsc_permissao',
    ];

    /**
     * Constantes de perfis pré-definidos
     */
    const SUPER_ADMIN = 'c00b9ebc-7014-4d37-97dc-7875e55fff2a';
    const ADMIN_UNIDADE = 'c00b9ebc-7014-4d37-97dc-7875e55fff3b';
    const GESTOR_RESPONSAVEL = 'c00b9ebc-7014-4d37-97dc-7875e55fff4c';
    const GESTOR_SUBSTITUTO = 'c00b9ebc-7014-4d37-97dc-7875e55fff5d';

    /**
     * Relacionamento: Usuários com este perfil
     */
    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'rel_users_tab_organizacoes_tab_perfil_acesso',
            'cod_perfil',
            'user_id',
            'cod_perfil',
            'id'
        )->withPivot('cod_organizacao', 'cod_plano_de_acao');
    }

    /**
     * Métodos auxiliares
     */

    /**
     * Verifica se é perfil de Super Admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->cod_perfil === self::SUPER_ADMIN;
    }

    /**
     * Verifica se é perfil de Admin de Unidade
     */
    public function isAdminUnidade(): bool
    {
        return $this->cod_perfil === self::ADMIN_UNIDADE;
    }

    /**
     * Verifica se é perfil de gestor (responsável ou substituto)
     */
    public function isGestor(): bool
    {
        return in_array($this->cod_perfil, [self::GESTOR_RESPONSAVEL, self::GESTOR_SUBSTITUTO]);
    }
}
