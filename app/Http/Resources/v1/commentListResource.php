<?php

namespace App\Http\Resources\v1;
use App\Http\Resources\v1\profileResource;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class commentListResource extends JsonResource
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
            'post_id' => $this->post_id,
            'owner_id' => $this->user_id,
            'hide' => $this->hide,
            'content' => $this->content,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'is_liked' => (bool) $this->is_liked,
            'likes_count' => $this->total_likes,
            'replies_count' => $this->replies_count,
            'owner_name' => $this->user->profile?->name ?? null,
            'owner_image' => $this->user->profile?->profile_image ?  Storage::disk('s3')->temporaryUrl($this->user->profile->profile_image, now()->addMinutes(10)) : null,
        ];
    }
}
