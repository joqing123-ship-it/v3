<?php


use App\Models\Post;
use App\Models\Comment;
use App\Models\Reply;

if (!function_exists('toggle_like')) {
    function toggle_like(string $type, int $id,int $user_id) //id is wheter post id or comment id
    {
        $modelClass = match ($type) {
            'post' => Post::class,
            'comment' => Comment::class,
            'reply' => Reply::class,
            default => abort(404),
        };

        $model = $modelClass::findOrFail($id);
        if(!$model){
            return   response()->json(
            [
                'message' => 'Not found',
                'status' => false,
                'data' => null
                ]
            );

        }

        $existingLike = $model->likes()->where('user_id', $user_id)->first();

        if ($existingLike) {
            $existingLike->delete();
            return response()->json(['status' => true,'message' => 'Unliked', 'data' => null]);
        } else {
            $model->likes()->create([
                'user_id' => $user_id,
                'likeable_id' => $id,
                'likeable_type' => $modelClass,
            ]);
            return response()->json(['status' => true,'message' => 'Liked', 'data' => null],);
        }
    }
};

