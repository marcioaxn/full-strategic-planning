<?php

namespace App\Models;

use App\Models\StrategicPlanning\PEI;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Documento em PDF do acervo (menu "Documentos").
 *
 * Sem unidade, o documento é institucional: todos os perfis o leem e só quem
 * edita o institucional (Super Admin ou Administrador da unidade raiz) o grava.
 * Com unidade, vale o escopo e a matriz do módulo "documentos" naquela unidade.
 */
class Documento extends Model implements Auditable
{
    use HasUuids, \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $table = 'strategic_planning.tab_documentos';

    protected $primaryKey = 'cod_documento';

    protected $keyType = 'string';

    public $incrementing = false;

    /** Onde os arquivos ficam: disco privado, fora da pasta pública. */
    public const DISCO = 'local';

    public const PASTA = 'documentos';

    /**
     * Tipos de documento, agrupados como aparecem no seletor. Cobrem os atos e
     * instrumentos com que a gestão pública federal registra o planejamento.
     *
     * @var array<string, array<int, string>>
     */
    public const TIPOS = [
        'Atos normativos' => [
            'Lei', 'Lei Complementar', 'Medida Provisória', 'Decreto', 'Decreto Legislativo',
            'Portaria', 'Portaria Conjunta', 'Portaria Interministerial', 'Instrução Normativa',
            'Instrução Normativa Conjunta', 'Resolução', 'Deliberação', 'Regimento Interno',
            'Estatuto', 'Ordem de Serviço',
        ],
        'Planejamento e gestão' => [
            'Plano Estratégico Institucional', 'Plano Plurianual (PPA)', 'Plano Tático', 'Plano Operacional',
            'Plano Diretor de TI (PDTI)', 'Plano de Integridade', 'Plano Anual de Contratações',
            'Política', 'Mapa Estratégico', 'Cadeia de Valor', 'Termo de Abertura',
        ],
        'Relatórios e prestação de contas' => [
            'Relatório de Gestão', 'Relatório de Atividades', 'Relatório de Monitoramento',
            'Relatório de Avaliação', 'Relatório de Auditoria', 'Prestação de Contas',
        ],
        'Instrumentos e expedientes' => [
            'Acordo de Cooperação Técnica', 'Convênio', 'Contrato', 'Termo de Referência',
            'Nota Técnica', 'Parecer', 'Despacho', 'Ofício', 'Memorando', 'Ata de Reunião',
        ],
        'Referência e orientação' => [
            'Manual', 'Guia', 'Cartilha', 'Apresentação', 'Estudo', 'Outro',
        ],
    ];

    protected $fillable = [
        'nom_documento',
        'dsc_tipo',
        'num_documento',
        'num_ano_referencia',
        'dte_documento',
        'dsc_origem',
        'txt_descricao',
        'dsc_link',
        'cod_pei',
        'cod_organizacao',
        'dsc_nome_arquivo',
        'dsc_caminho',
        'num_tamanho_bytes',
        'dsc_hash_sha256',
        'cod_usuario',
    ];

    protected function casts(): array
    {
        return [
            'dte_documento' => 'date',
            'num_ano_referencia' => 'integer',
            'num_tamanho_bytes' => 'integer',
        ];
    }

    /** @return array<int, string> */
    public static function tiposPlanos(): array
    {
        return array_merge(...array_values(self::TIPOS));
    }

    public function pei(): BelongsTo
    {
        return $this->belongsTo(PEI::class, 'cod_pei', 'cod_pei');
    }

    public function organizacao(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'cod_organizacao', 'cod_organizacao');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usuario', 'id');
    }

    /** O que o usuário pode ver: institucionais e os das unidades do escopo dele. */
    public function scopeVisiveisPara(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($user) {
            $q->whereNull('cod_organizacao')
                ->orWhereIn('cod_organizacao', $user->organizacaoIdsPermitidas());
        });
    }

    public function tamanhoLegivel(): string
    {
        $bytes = (int) $this->num_tamanho_bytes;

        return $bytes >= 1048576
            ? number_format($bytes / 1048576, 1, ',', '.').' MB'
            : number_format(max(1, $bytes / 1024), 0, ',', '.').' KB';
    }
}
