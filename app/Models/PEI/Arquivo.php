<?php

namespace App\Models\PEI;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Arquivo extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Tabela do banco de dados
     */
    protected $table = 'pei.tab_arquivos';

    /**
     * Chave primária
     */
    protected $primaryKey = 'cod_arquivo';

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
        'cod_evolucao_indicador',
        'txt_assunto',
        'data',
        'dsc_nome_arquivo',
        'dsc_tipo',
    ];

    /**
     * Relacionamento: Evolução do Indicador
     */
    public function evolucaoIndicador(): BelongsTo
    {
        return $this->belongsTo(EvolucaoIndicador::class, 'cod_evolucao_indicador', 'cod_evolucao_indicador');
    }

    /**
     * Métodos auxiliares
     */

    /**
     * Obter extensão do arquivo
     */
    public function getExtensao(): string
    {
        return pathinfo($this->dsc_nome_arquivo, PATHINFO_EXTENSION);
    }

    /**
     * Verifica se é imagem
     */
    public function isImagem(): bool
    {
        $extensoes = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'];
        return in_array(strtolower($this->getExtensao()), $extensoes);
    }

    /**
     * Verifica se é PDF
     */
    public function isPdf(): bool
    {
        return strtolower($this->getExtensao()) === 'pdf';
    }

    /**
     * Scopes
     */

    /**
     * Scope: Por tipo
     */
    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('dsc_tipo', $tipo);
    }

    /**
     * Scope: Arquivos recentes
     */
    public function scopeRecentes($query, int $dias = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($dias))
                     ->orderBy('created_at', 'desc');
    }
}
