<?php

namespace App\Models\ActionPlan;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Raci extends Model
{
    use HasUuids;

    protected $table = 'action_plan.tab_raci';

    protected $primaryKey = 'cod_raci';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_plano_de_acao',
        'cod_entrega',
        'user_id',
        'dsc_papel',
    ];

    public const PAPEIS = [
        'R' => 'Responsável (executa)',
        'A' => 'Aprovador (accountability)',
        'C' => 'Consultado (input)',
        'I' => 'Informado (resultado)',
    ];

    public function plano(): BelongsTo
    {
        return $this->belongsTo(PlanoDeAcao::class, 'cod_plano_de_acao', 'cod_plano_de_acao');
    }

    public function entrega(): BelongsTo
    {
        return $this->belongsTo(Entrega::class, 'cod_entrega', 'cod_entrega');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
