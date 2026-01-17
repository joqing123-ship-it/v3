<?php

namespace App\Http\Controllers\v1;
use App\Http\Resources\v1\profileResource;
use App\Models\Profile;

use App\Http\Requests\StoreprofileRequest;
use App\Http\Controllers\v1\Controller;
use App\Http\Resources\v1\userResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $personal_profile = $request->user()->profile; // get the profile of the authenticated user
        return new profileResource($personal_profile);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreprofileRequest $request)
  {
        $image_uploaded_path = null;

        return Profile::create([
            'name' => $request->name,
            'profile_image' => $image_uploaded_path,
            'phone' => $request->phone,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Profile $profile)
    {
        Gate::authorize('modify', $profile);
        return new profileResource($profile);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Profile $profile)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Profile $profile)
    {
        try{
   

        $data = $request->validate([
            'name' => 'sometimes|required|nullable',
            'phone' => 'sometimes|nullable',
            'profile_image' => 'sometimes|nullable',
            'remove_image' => 'sometimes|required',
        ]);
           if($request->remove_image == 'true'){
            // if($post->image != null){
            //     Storage::disk('public')->delete($post->image);
            // }
            $profile->profile_image = null;
        }
        if($request->hasFile('profile_image')){
            // if($post->image != null){
            //     Storage::disk('public')->delete($post->image);
            // }
            $uploadFolder = 'profile';
            $image = $request->file('profile_image');
            $image_name = time() . '_' . $image->getClientOriginalName();
            $image_uploaded_path = $image->storeAs($uploadFolder , $image_name, 's3');
            $profile->profile_image = $image_uploaded_path;
        }

            $profile->name = $data['name'];


            $profile->phone = $data['phone'];

        $profile->save();




     return response()->json([
        'status' => true,
        'message' => 'Profile updated successfully',
        'data' => new profileResource($profile)
     ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Profile update failed: ' . $e->getMessage(),
        ], 500);
    }
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Profile $profile)
    {
        Gate::authorize('modify', $profile);
        $profile->delete();
        return response()->json([
            'message' => 'Profile deleted successfully',
        ], 200);
    }
}
