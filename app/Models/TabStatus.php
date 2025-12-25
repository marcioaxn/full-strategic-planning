<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TabStatus extends Model
{
    use HasFactory, HasUuids;

    /**
     * Tabela do banco de dados
     */
    protected $table = 'tab_status';

    /**
     * Chave primária
     */
    protected $primaryKey = 'cod_status';

    /**
     * Tipo da chave primária
     */
    protected $keyType = 'string';

    /**
     * Chave primária não é auto-incremental
     */
    public $incrementing = false;

    /**
     * Indica que a tabela não tem timestamps
     */
    public $timestamps = false;

    /**
     * Atributos mass assignable
     */
    protected $fillable = [
        'dsc_status',
    ];

    /**
     * Métodos auxiliares
     */

    /**
     * Obter status por descrição
     */
    public static function porDescricao(string $descricao): ?self
    {
        return static::where('dsc_status', $descricao)->first();
    }

    /**
     * Scopes
     */

    /**
     * Scope: Buscar por descrição (parcial)
     */
    public function scopeBuscarPorDescricao($query, string $termo)
    {
        return $query->where('dsc_status', 'ILIKE', "%{$termo}%");
    }

    /**
     * Scope: Ordenar por descrição
     */
    public function scopeOrdenadoPorDescricao($query, string $direcao = 'asc')
    {
        return $query->orderBy('dsc_status', $direcao);
    }
}
