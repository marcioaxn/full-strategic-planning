<?php

namespace App\Models\ActionPlan;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model de Comentário em Entrega.
 */
class EntregaComentario extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Tabela do banco de dados
     */
    protected $table = 'tab_entrega_comentarios';

    /**
     * Chave primária
     */
    protected $primaryKey = 'cod_comentario';

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
        'cod_entrega',
        'cod_usuario',
        'cod_comentario_pai',
        'dsc_comentario',
        'json_mencoes',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'json_mencoes' => 'array',
    ];

    // ========================================
    // RELACIONAMENTOS
    // ========================================

    /**
     * Relacionamento: Entrega
     */
    public function entrega(): BelongsTo
    {
        return $this->belongsTo(Entrega::class, 'cod_entrega', 'cod_entrega');
    }

    /**
     * Relacionamento: Usuário que comentou
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usuario', 'id');
    }

    /**
     * Relacionamento: Comentário pai (se for uma resposta)
     */
    public function comentarioPai(): BelongsTo
    {
        return $this->belongsTo(EntregaComentario::class, 'cod_comentario_pai', 'cod_comentario');
    }

    /**
     * Relacionamento: Respostas a este comentário
     */
    public function respostas(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EntregaComentario::class, 'cod_comentario_pai', 'cod_comentario')
                    ->orderBy('created_at', 'asc');
    }

    // ========================================
    // MÉTODOS AUXILIARES
    // ========================================

    /**
     * Retorna lista de usuários mencionados
     */
    public function getUsuariosMencionados(): array
    {
        if (empty($this->json_mencoes)) {
            return [];
        }

        return User::whereIn('id', $this->json_mencoes)->get()->all();
    }

    /**
     * Verifica se um usuário foi mencionado
     */
    public function mencionou(int $userId): bool
    {
        return in_array($userId, $this->json_mencoes ?? []);
    }
}
