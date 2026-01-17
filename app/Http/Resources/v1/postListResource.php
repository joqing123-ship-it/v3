<?php

namespace App\Http\Resources\v1;
use App\Http\Resources\v1\profileResource;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class postListResource extends JsonResource
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
            'title' => $this->title,
            'content' => $this->content,
            'hide' => $this->hide,
            'image' => $this->image ? Storage::disk('s3')->temporaryUrl($this->image, now()->addMinutes(10)) : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'is_liked' => (bool) $this->is_liked,
            'likes_count' => $this->total_likes,
            'comments_count' => $this->comments_count ?? 0,
            'owner_id' => $this->user->id,
            'owner_role' => $this->user->role,
            'owner_name' => $this->user->profile?->name ?? null,
            'owner_image' => $this->user->profile?->profile_image ?  Storage::disk('s3')->temporaryUrl($this->user->profile->profile_image, now()->addMinutes(10)) : null,
            'state' => $this->state ?? null,
            'city' => $this->city ?? null,
        ];
    }
}
