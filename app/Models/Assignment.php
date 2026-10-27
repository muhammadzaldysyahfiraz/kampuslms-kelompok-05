<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'instructions',
        'due_at',
        'max_score',
        'allow_late',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'max_score' => 'integer',
            'allow_late' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // Hapus berkas fisik seluruh submission sebelum cascadeOnDelete di database menghapus barisnya (Minggu 9).
        static::deleting(function (Assignment $assignment) {
            foreach ($assignment->submissions()->get() as $submission) {
                if ($submission->file_path) {
                    \Illuminate\Support\Facades\Storage::disk('local')->delete($submission->file_path);
                }
            }
        });
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function grades(): HasManyThrough
    {
        return $this->hasManyThrough(
            Grade::class,
            Submission::class
        );
    }
}