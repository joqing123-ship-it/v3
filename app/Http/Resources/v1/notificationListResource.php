<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class notificationListResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        $type = $this->data['type'] ?? null;
        if($type === 'comment') {
        return [
            'id' => $this->id,
            'type' => $this->data["type"],
            'title' => $this->data["title"],
            'message' => $this->data["message"],
            'comment_id' => $this->data["comment_id"],
            'post_id' => $this->data["post_id"],
            'created_at' => $this->created_at,
            'is_read' => $this->read_at != null? true : false,
        ];
    }
    else if($type === 'like') {

    }
    }
}
