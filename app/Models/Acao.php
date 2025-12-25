<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Acao extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Tabela do banco de dados
     */
    protected $table = 'acoes';

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
        'table_id',
        'user_id',
        'table',
        'acao',
    ];

    /**
     * Relacionamento: Usuário que executou a ação
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Scopes
     */

    /**
     * Scope: Por tabela
     */
    public function scopePorTabela($query, string $tabela)
    {
        return $query->where('table', $tabela);
    }

    /**
     * Scope: Por registro (table_id)
     */
    public function scopePorRegistro($query, string $tableId)
    {
        return $query->where('table_id', $tableId);
    }

    /**
     * Scope: Por usuário
     */
    public function scopePorUsuario($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Ações recentes
     */
    public function scopeRecentes($query, int $dias = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($dias))
                     ->orderBy('created_at', 'desc');
    }
}
