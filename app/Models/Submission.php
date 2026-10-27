<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Submission extends Model
{
    use HasFactory;

    // Jalur berkas internal hanya dipakai backend dan tidak diekspos sebagai JSON.
    protected $hidden = [
        'file_path',
    ];

    protected $fillable = [
        'note',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'is_late' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Submission $submission) {
            if ($submission->file_path) {
                \Illuminate\Support\Facades\Storage::disk('local')->delete($submission->file_path);
            }
        });
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function grade(): HasOne
    {
        return $this->hasOne(Grade::class);
    }
}
