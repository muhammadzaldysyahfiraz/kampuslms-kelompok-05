<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class CourseResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'sks' => $this->sks,
            'status' => $this->status,
            'lecturer' => $this->whenLoaded('lecturer', fn () => [
                'id' => $this->lecturer->id,
                'name' => $this->lecturer->name,
            ]),
            'counts' => [
                'materials' => $this->whenCounted('materials'),
                'assignments' => $this->whenCounted('assignments'),
            ],
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
