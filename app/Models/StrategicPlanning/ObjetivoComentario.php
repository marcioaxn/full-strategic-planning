<?php

namespace App\Models\StrategicPlanning;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ObjetivoComentario extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'tab_objetivo_comentarios';
    protected $primaryKey = 'cod_comentario';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_objetivo',
        'user_id',
        'dsc_comentario',
        'cod_comentario_pai',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function objetivo(): BelongsTo
    {
        return $this->belongsTo(Objetivo::class, 'cod_objetivo', 'cod_objetivo');
    }
}
