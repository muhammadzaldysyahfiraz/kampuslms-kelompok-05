<?php

namespace App\Listeners;

use App\Events\GradeReleased;
use App\Notifications\GradeReleasedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendGradeNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public int $backoff = 10;

    public function handle(GradeReleased $event): void
    {
        $grade = $event->grade;
        $grade->loadMissing(['submission.student', 'submission.assignment.course']);

        $student = $grade->submission->student;
        if ($student?->role === 'mahasiswa') {
            $student->notify(new GradeReleasedNotification($grade));
        }
    }

    public function failed(GradeReleased $event, Throwable $exception): void
    {
        Log::error('Notifikasi nilai gagal dikirim.', [
            'grade_id' => $event->grade->id,
            'exception' => $exception->getMessage(),
        ]);
    }
}