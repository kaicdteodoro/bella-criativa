<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportRun extends Model
{
    protected $fillable = [
        'user_id',
        'source',
        'initiated_via',
        'file_path',
        'original_filename',
        'dry_run',
        'resume',
        'limit',
        'status',
        'total_rows',
        'created_count',
        'updated_count',
        'skipped_count',
        'failed_count',
        'summary',
        'results',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'dry_run' => 'boolean',
            'resume' => 'boolean',
            'limit' => 'integer',
            'total_rows' => 'integer',
            'created_count' => 'integer',
            'updated_count' => 'integer',
            'skipped_count' => 'integer',
            'failed_count' => 'integer',
            'summary' => 'array',
            'results' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'running' => 'Em andamento',
            'completed' => 'Concluída',
            'completed_with_errors' => 'Concluída com falhas',
            'failed' => 'Falhou',
            default => ucfirst(str_replace('_', ' ', (string) $this->status)),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'running' => 'warning',
            'completed' => 'success',
            'completed_with_errors' => 'warning',
            'failed' => 'danger',
            default => 'gray',
        };
    }

    public function getFailedItemsAttribute(): array
    {
        return collect($this->results ?? [])
            ->filter(fn (array $result): bool => ($result['action'] ?? null) === 'failed')
            ->values()
            ->all();
    }
}
