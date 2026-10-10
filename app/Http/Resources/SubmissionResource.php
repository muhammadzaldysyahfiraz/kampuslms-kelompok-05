<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class SubmissionResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'assignment_id' => $this->assignment_id,
            'student' => $this->whenLoaded('student', fn () => [
                'id' => $this->student->id,
                'name' => $this->student->name,
                'nim_nip' => $this->student->nim_nip,
            ]),
            'original_name' => $this->original_name,
            'file_size' => $this->file_size,
            'note' => $this->note,
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'is_late' => (bool) $this->is_late,
            'grade' => $this->whenLoaded('grade', fn () => new GradeResource($this->grade)),
        ];
    }
}
