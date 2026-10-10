<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class GradeResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'submission_id' => $this->submission_id,
            'score' => (float) $this->score,
            'feedback' => $this->feedback,
            'graded_at' => $this->graded_at?->toIso8601String(),
            'grader' => $this->whenLoaded('grader', fn () => [
                'id' => $this->grader->id,
                'name' => $this->grader->name,
            ]),
        ];
    }
}
