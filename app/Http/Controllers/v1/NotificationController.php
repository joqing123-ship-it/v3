<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\v1\Controller;
use App\Models\Notification;
use App\Http\Resources\v1\notificationListResource;
use Illuminate\Http\Request;
class NotificationController extends Controller
{
    public function index(){
        // return request()->user()->notifications;
        try{
            return response()->json(['status' => true,'message' => 'Notification fetched successfully','data' => notificationListResource::collection(request()->user()->notifications()->paginate(10))], 200);
        }
        catch(\Exception $e){
            return response()->json(['status' => false,'message' => 'Notification not fetched successfully', 'error' => $e->getMessage()], 500);
        }
    }

    public function getUnreadCount(Request $request){
    $user = request()->user();
    $count = $user->unreadNotifications()->count();
    return response()->json(['status' => true,'message' => 'Unread notification count fetched successfully','data' => ['unread_count' => $count]], 200);
    }

    public function markAsRead(Request $request){
       $data = $request->validate([
            'notification_id' => 'required',
        ]);
        $notification = request()->user()->notifications()->where('id', $data['notification_id'])->first();
        if($notification){
            $notification->markAsRead();
            return response()->json(['status' => true,'message' => 'Notification marked as read'], 200);
    }
    return response()->json(['status' => false,'message' => 'Notification not found'], 404);
    //
}
}
