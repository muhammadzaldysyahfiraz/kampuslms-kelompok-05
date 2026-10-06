<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaterialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'course_id'     => $this->course_id,
            'title'         => $this->title,
            'description'   => $this->description,
            'type'          => $this->type,
            'original_name' => $this->original_name,
            'file_size'     => $this->file_size,
            'mime_type'     => $this->mime_type,
            'external_url'  => $this->external_url,
            'uploader'      => $this->whenLoaded('uploader', fn () => [
                'id'   => $this->uploader->id,
                'name' => $this->uploader->name,
            ]),
            'created_at'    => $this->created_at?->toIso8601String(),
        ];
    }
}