<?php

namespace App\Http\Controllers\v1;
use App\Http\Controllers\v1\Controller;
use App\Models\Reply;
use Illuminate\Http\Request;
use App\Http\Resources\v1\replyListResource;
class ReplyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return Reply::all();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

    }

    public function commentReplies(Request $request){
        $data = $request->validate([
            'comment_id' => 'required'
        ]);
        try{
         $newReply = Reply::query()
            ->where("hide", false)
            ->where('comment_id', $data['comment_id'])
            ->with('user.profile')
            ->with('taggedUser.profile')
            ->withCount(["likes as is_liked" => function ($query) use ($request){ $query->where("user_id", $request->user()->id);}])
            ->withCount("likes as total_likes")
            ->paginate(10);

        if(empty($newReply)){
            return response()->json(['status' => false,'message' => 'empty comment replies','data' => []], 201);
        }
        return response()->json(['status' => true,'message' => 'Comment replies fetched successfully','data' => replyListResource::collection($newReply)], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => false,'message' => 'Comment replies not fetched successfully', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         $data = $request->validate(
            [
                'taged_user_id' => 'required',
                'comment_id' => 'required',
                'content' => 'required|string',
            ]
            );

            try{
                $reply = Reply::create(
 [
                'user_id' => $request->user()->id,
                'taged_user_id' => $data['taged_user_id'],
                'comment_id' => $data['comment_id'],
                'content' => $data['content'],
            ]
            );
            $newReply = $reply::query()
            ->where('id', $reply->id)
            ->with('user.profile')
            ->with('taggedUser.profile')
            ->withCount(["likes as is_liked" => function ($query) use ($request){ $query->where("user_id", $request->user()->id);}])
            ->withCount("likes as total_likes")
            ->first();
            return response()->json(['status' => true,'message' => 'Reply created successfully','data' => new replyListResource($newReply)], 201);
            } catch (\Exception $e) {
            return response()->json(['status' => false,'message' => 'reply not created successfully', 'error' => $e->getMessage()], 500);
            }

    }

    /**
     * Display the specified resource.
     */
    public function show(Reply $reply)
    {
        return $reply;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reply $reply)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reply $reply)
    {
        $data = $request->validate(
            [
                'content' => 'required|string',
            ]
            );

        try{
           $reply->update(
            [
                'content' => $data['content'],
            ]
            );
            return response()->json(['status' => true,'message' => 'Reply updated successfully','data' =>  new replyListResource($reply)], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => false,'message' => 'Reply not updated successfully', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reply $reply)
    {
try{
    $reply->delete();
    return response()->json(['status' => true,'message' => 'Reply deleted successfully','data' => null], 200);
}
catch (\Exception $e) {
return response()->json(['status' => false,'message' => 'Reply not deleted successfully', 'error' => $e->getMessage()], 500);
}
}
}

?>
