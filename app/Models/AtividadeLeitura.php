<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Até quando o usuário já viu a aba "Atividade" do sino (uma linha por usuário).
 *
 * Não é auditado: abrir o sino não é ação de negócio, e auditá-lo encheria a
 * própria trilha que o feed lê.
 */
class AtividadeLeitura extends Model
{
    protected $table = 'pei.tab_atividade_leitura';

    protected $primaryKey = 'user_id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['user_id', 'dte_visto_ate'];

    protected function casts(): array
    {
        return ['dte_visto_ate' => 'datetime'];
    }
}
