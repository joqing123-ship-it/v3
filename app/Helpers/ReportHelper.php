<?php


use App\Models\Post;
use App\Models\Comment;
use App\Models\Reply;

if (!function_exists('toggle_report')) {
    function toggle_report(string $type, int $id,int $user_id,string $reason) //id is wheter post id or comment id
    {
        $modelClass = match ($type) {
            'post' => Post::class,
            'comment' => Comment::class,
            'reply' => Reply::class,
            default => abort(404),
        };

    $model = $modelClass::find($id);

     if($model){

        $model->hide = true;
        $model->save();
        $model->reports()->create([
            'reason' => $reason,
            'user_id' => $user_id,
            'reportable_id' => $id,
            'reportable_type' => $modelClass,
            ]);
    }
    else{
    return response()->json(['status' => false,'message' => 'not found', 'data' => null],);

    }
    return response()->json(['status' => true,'message' => 'Reported', 'data' => null],);



    }
};

if (!function_exists('release_reported')) {
    function release_reported(string $type, int $id) //id is wheter post id or comment id
    {
        $modelClass = match ($type) {
            'post' => Post::class,
            'comment' => Comment::class,
            'reply' => Reply::class,
            default => abort(404),
        };

        $model = $modelClass::findOrFail($id);
        if($model){
        $model->hide = false;
        $model->save();
        }
        return response()->json(['status' => true,'message' => 'Reported', 'data' => null],);

    }



}

