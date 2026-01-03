<?php

namespace App\Models\ActionPlan;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * Model de Anexo em Entrega.
 */
class EntregaAnexo extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Tabela do banco de dados
     */
    protected $table = 'tab_entrega_anexos';

    /**
     * Chave primária
     */
    protected $primaryKey = 'cod_anexo';

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
        'dsc_nome_arquivo',
        'dsc_caminho',
        'dsc_mime_type',
        'num_tamanho_bytes',
        'dsc_descricao',
        'dsc_thumbnail',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'num_tamanho_bytes' => 'integer',
    ];

    /**
     * Tipos MIME de imagem suportados
     */
    public const MIME_IMAGENS = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml',
    ];

    /**
     * Tipos MIME de documento
     */
    public const MIME_DOCUMENTOS = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/plain',
        'text/csv',
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
     * Relacionamento: Usuário que fez upload
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usuario', 'id');
    }

    // ========================================
    // MÉTODOS AUXILIARES
    // ========================================

    /**
     * Verifica se é uma imagem
     */
    public function isImagem(): bool
    {
        return in_array($this->dsc_mime_type, self::MIME_IMAGENS);
    }

    /**
     * Verifica se é um documento
     */
    public function isDocumento(): bool
    {
        return in_array($this->dsc_mime_type, self::MIME_DOCUMENTOS);
    }

    /**
     * Retorna a URL do arquivo
     */
    public function getUrl(): string
    {
        return asset('storage/' . $this->dsc_caminho);
    }

    /**
     * Retorna tamanho formatado
     */
    public function getTamanhoFormatado(): string
    {
        $bytes = $this->num_tamanho_bytes;
        $units = ['B', 'KB', 'MB', 'GB'];
        $index = 0;

        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return round($bytes, 2) . ' ' . $units[$index];
    }

    /**
     * Retorna ícone apropriado para o tipo
     */
    public function getIcone(): string
    {
        return match (true) {
            $this->isImagem() => 'bi-image',
            str_contains($this->dsc_mime_type, 'pdf') => 'bi-file-pdf',
            str_contains($this->dsc_mime_type, 'word') => 'bi-file-word',
            str_contains($this->dsc_mime_type, 'excel') || str_contains($this->dsc_mime_type, 'spreadsheet') => 'bi-file-excel',
            str_contains($this->dsc_mime_type, 'powerpoint') || str_contains($this->dsc_mime_type, 'presentation') => 'bi-file-ppt',
            str_contains($this->dsc_mime_type, 'text') => 'bi-file-text',
            default => 'bi-file-earmark',
        };
    }

    /**
     * Retorna extensão do arquivo
     */
    public function getExtensao(): string
    {
        return pathinfo($this->dsc_nome_arquivo, PATHINFO_EXTENSION);
    }
}
