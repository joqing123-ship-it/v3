<?php

namespace App\Http\Controllers\v1;

use App\Http\Resources\v1\commentListResource;
use App\Http\Controllers\v1\Controller;
use App\Models\Comment;
use App\Http\Requests\UpdatecommentRequest;
use App\Notifications\NewCommentNotification;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Comment::all();
    }
       public function postComments(Request $request){
        $data = $request->validate([
            'post_id' => 'required'
        ]);

        $postComments =  Comment::query()
        ->where('post_id', $data['post_id'])
        ->with('user.profile')
        ->with(relations: 'likes.user.profile')
        ->withCount(["likes as is_liked" => function ($query) use ($request){ $query->where("user_id", $request->user()->id);}])
        ->withCount("likes as total_likes")
        ->withCount("replies as replies_count")
        ->orderBy('total_likes', 'desc')
        ->get();
        return response()->json(['status' => true,'message' => 'Post comments fetched successfully','data' => commentListResource::collection($postComments)], 201);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate(
            [
                'post_id' => 'required|exists:posts,id',
                'content' => 'required|string',
            ]
            );

        try{
           $comment = Comment::create(
            [
                'user_id' => $request->user()->id,
                'post_id' => $data['post_id'],
                'content' => $data['content'],
            ]
            );

        $postOwner = $comment->post->user;


        $postOwner->notify(new NewCommentNotification($comment));

        $newComment = Comment::with('user.profile')
        ->withCount(["likes as is_liked" => function ($query) use ($request){ $query->where("user_id", $request->user()->id);}])
        ->withCount("likes as total_likes")
        ->where('id', $comment->id)
        ->first();
        return response()->json(['status' => true,'message' => 'Comment created successfully','data' =>   new commentListResource($newComment)], 201);
    } catch (\Exception $e) {
        return response()->json(['status' => false,'message' => 'Comment not created successfully', 'error' => $e->getMessage()], 500);
    }


    }
    //  public function store(Request $request)
    // {
    //     $data = $request->validate(
    //         [
    //             'comment_id' => 'required|integer',
    //             'content' => 'required|string',
    //             'target_user_id' => 'required|integer',
    //         ]
    //         );

    //     try{
    //        $reply = reply::create(
    //         [
    //             'user_id' => $request->user()->id,
    //             'comment_id' => $data['comment_id'],
    //             'content' => $data['content'],
    //             'target_user_id' => $data['target_user_id'],
    //         ]
    //         );
    //     $newReply = $reply->with('user.profile')
    //     ->with( 'taggedUser.profile')
    //     ->withCount(["likes as is_liked" => function ($query) use ($request){ $query->where("user_id", $request->user()->id);}])
    //     ->withCount("likes as total_likes")
    //     ->first();
    //     return response()->json(['status' => true,'message' => 'Comment created successfully','data' =>   $newReply], 201);
    // } catch (\Exception $e) {
    //     return response()->json(['status' => false,'message' => 'Comment not created successfully', 'error' => $e->getMessage()], 500);
    // }


    // }

    /**
     * Display the specified resource.
     */
    public function show(Comment $comment)
    {
        return $comment;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Comment $comment)
    {
        try{
            $data = $request->validate([
            'content' => 'required|string',
        ]);
        $comment->update([
            'content' => $data['content'],
        ]);
        return response()->json(['status' => true,'message' => 'Comment updated successfully','data' =>  new commentListResource($comment)], 200);

    } catch (\Exception $e) {
        return response()->json(['status' => false,'message' => 'Comment not updated successfully', 'error' => $e->getMessage()], 500);
    }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment)
    {

        try{
            $comment->delete();
            return response()->json(['status' => true,'message' => 'Comment deleted successfully','data' => null], 200);
        }
        catch (\Exception $e) {
            return response()->json(['status' => false,'message' => 'Comment not deleted successfully', 'error' => $e->getMessage()], 500);
        }
    }
}
