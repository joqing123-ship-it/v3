<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class userResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
    // Determine the user's name: prioritize profile name, then display_name, then email
        $name = $this->profile ? $this->profile->name : ($this->display_name ?? '');
        // For profile image, use Huawei avatar if no local profile image exists
        $profileImage = null;
        if ($this->profile && $this->profile->profile_image) {
            $profileImage = Storage::disk('s3')->temporaryUrl($this->profile->profile_image, now()->addMinutes(10));
        } elseif ($this->avatar_uri) {
            $profileImage = $this->avatar_uri; // Use Huawei avatar directly
        }

        return [
            'id' => $this->id,
            'profile_id' => $this->profile ? $this->profile->id : 0,
            'email' => $this->email,
            'name' => $this->profile->name ?? $name,
            'description' => $this->profile ? $this->profile->description : '',
            'profile_image' => $profileImage,
            'auth_provider' => $this->auth_provider, // Show authentication method
            'role' => $this->role,
            'phone' => $this->profile ? $this->profile->phone : null,
        ];
    }
}
