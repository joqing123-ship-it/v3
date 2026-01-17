<?php

namespace App\Http\Resources\v1;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class plantResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'diseaseId' => $this->diseaseId,
            'image' => $this->image ? Storage::disk('s3')->temporaryUrl($this->image,now()->addMinutes(10)) : null,
            'confidence' => $this->confidence,
            'created_at' => $this->created_at,
        ];
    }
}
