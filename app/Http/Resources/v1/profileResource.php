<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
class profileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'profile_image' => $this->profile_image // check if profile_image is not null
            ? Storage::disk('s3')->temporaryUrl($this->profile_image, now()->addMinutes(10)) : null, // if you want to return full URL // make it to become http://127.0.0.1:8000/storage/profile/avatar.png
        ];
    }

}
