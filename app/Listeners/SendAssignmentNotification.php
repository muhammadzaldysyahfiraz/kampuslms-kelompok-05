<?php

namespace App\Listeners;

use App\Events\AssignmentPublished;
use App\Notifications\NewAssignmentNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendAssignmentNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public int $backoff = 10;

    public function handle(AssignmentPublished $event): void
    {
        $assignment = $event->assignment;
        $assignment->loadMissing('course');

        $students = $assignment->course->students()
            ->where('role', 'mahasiswa')
            ->get();

        Notification::send($students, new NewAssignmentNotification($assignment));
    }

    public function failed(AssignmentPublished $event, Throwable $exception): void
    {
        Log::error('Notifikasi tugas baru gagal dikirim.', [
            'assignment_id' => $event->assignment->id,
            'exception' => $exception->getMessage(),
        ]);
    }
}