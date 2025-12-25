<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    /** @use HasFactory<\Database\Factories\LeadFactory> */
    use HasFactory;

    public const STATUSES = [
        'new' => 'Novo',
        'in_progress' => 'Em andamento',
        'won' => 'Ganho',
        'lost' => 'Perdido',
    ];

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'entity',
        'status',
        'email',
        'phone',
        'notes',
    ];

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return [
            'new' => 'bg-primary-subtle text-primary',
            'in_progress' => 'bg-warning-subtle text-warning',
            'won' => 'bg-success-subtle text-success',
            'lost' => 'bg-danger-subtle text-danger',
        ][$this->status] ?? 'bg-secondary-subtle text-secondary';
    }
}
