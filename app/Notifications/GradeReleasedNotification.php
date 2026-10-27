<?php

namespace App\Notifications;

use App\Models\Grade;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GradeReleasedNotification extends Notification
{
    use Queueable;

    public function __construct(public Grade $grade)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $this->grade->loadMissing('submission.assignment');
        $assignment = $this->grade->submission->assignment;

        return [
            'grade_id' => $this->grade->id,
            'assignment_id' => $assignment->id,
            'assignment_title' => $assignment->title,
        ];
    }
}