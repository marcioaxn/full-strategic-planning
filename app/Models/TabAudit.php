<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TabAudit extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Tabela do banco de dados
     */
    protected $table = 'tab_audit';

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
        'acao',
        'antes',
        'depois',
        'table',
        'column_name',
        'data_type',
        'table_id',
        'ip',
        'user_id',
        'dte_expired_at',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'dte_expired_at' => 'datetime',
    ];

    /**
     * Relacionamento: Usuário que executou a ação
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Métodos auxiliares
     */

    /**
     * Verifica se auditoria está expirada
     */
    public function isExpirado(): bool
    {
        if (!$this->dte_expired_at) {
            return false;
        }

        return now()->greaterThan($this->dte_expired_at);
    }

    /**
     * Obter diferenças em formato legível
     */
    public function getDiferencas(): array
    {
        return [
            'coluna' => $this->column_name,
            'tipo' => $this->data_type,
            'antes' => $this->antes,
            'depois' => $this->depois,
            'acao' => $this->acao,
        ];
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
     * Scope: Por tipo de ação
     */
    public function scopePorAcao($query, string $acao)
    {
        return $query->where('acao', $acao);
    }

    /**
     * Scope: Por coluna
     */
    public function scopePorColuna($query, string $coluna)
    {
        return $query->where('column_name', $coluna);
    }

    /**
     * Scope: Auditorias recentes
     */
    public function scopeRecentes($query, int $dias = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($dias))
                     ->orderBy('created_at', 'desc');
    }

    /**
     * Scope: Não expiradas
     */
    public function scopeNaoExpiradas($query)
    {
        return $query->where(function($q) {
            $q->whereNull('dte_expired_at')
              ->orWhere('dte_expired_at', '>', now());
        });
    }

    /**
     * Scope: Expiradas
     */
    public function scopeExpiradas($query)
    {
        return $query->whereNotNull('dte_expired_at')
                     ->where('dte_expired_at', '<=', now());
    }
}
