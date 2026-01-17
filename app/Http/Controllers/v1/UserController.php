<?php

namespace App\Http\Controllers\v1;
use App\Http\Controllers\v1\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Mail\WelcomeEmail;
use Illuminate\Support\Facades\Mail;
use App\Http\Resources\v1\userResource;
use App\Mail\OtpEmail;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return userResource::collection(
            User::latest()->paginate(5)
        );
        // return User::paginate(10);
    }
    public function getLoginUserInfo(Request $request)
    {
        $user = $request->user();

        if($user != null){
           return  response()->json([
            'status' => true,
            'message' => 'user retrieved successfully',
            'data' => new userResource($user)
           ]);
        }
        return response()->json([
            'status' => false,
            'message' => 'No authenticated user',
            'data' => null
        ], 401);
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
    public function store(Request $request)
    {
    // try{
        try{
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|max:255',
            'role' => 'required|string',
            'password' => 'required|string',
        ]);
        $exists = User::where('email', $data['email'])->exists();
        if($exists === true)
        {
            return response()->json([
                'exists' => true,
                'message' => 'Email already in use',
            ], );
        }



        $userData =[
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role'=> $data['role'],
        ];

        $user = User::create( $userData );

        $user->profile()->create(
            [
                 'name' => $request->name
            ]
        );
    //     if($request->role == 'worker'){
    //         $user->worker()->create(
    //             [
    //                 'department' => $request->department ?? 'general',
    //             ]
    //         );
    //     }
    //       $token  = $user->createToken('mobile')->plainTextToken;
    //     // Mail::to($user->email)->send(new WelcomeEmail($user, $password));
        return response()->json([
            'status' => true,
            'message' => 'User created successfully',
            'data' =>
            [
                "id" => $user->id,
                "email" => $user->email,
                "role" => $user->role,
                "name" => $user->profile->name,
            ]
        ]);
    }catch(\Exception $e){
            return response()->json([
            'status' => false,
            'message' => 'Server error: ' . $e->getMessage(),
        ], 500);
    }
    }
     public function register(Request $request)
    {
    try{
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string',
        ]);
        // $randomPassword = Str::random(12);
        // if($data['email'])

        $password = $data['password'];
        // $userData = [
        //     'name' =>  $data['name'],
        //     'email' => $data['email'],
        //     'password' => Hash::make($password),
        //     'department' => $data['department'] ?? 'general',
        // ];
        $exists = User::where('email', $data['email'])->exists();
        if($exists === true)
        {
            return response()->json([
                'status' => false,
                'message' => 'Email already in use',
            ], );
        }
        $user = User::create( [
            'name' =>  $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($password),
            'department' => $data['department'] ?? 'general',
        ] );
        $user->profile()->create(
            [
                 'name' => $request->name
            ]
        );


        if($request->role == 'worker'){
            $user->worker()->create(
                [
                    'department' => $request->department ?? 'general',
                ]
            );
        }
        // $otp = rand(1000, 9999); // 4-digit OTP

        // $user->otp = $otp;

        // $user->save();
        // Mail::to($user->email)->send( new OtpEmail(otp: $otp));

        //   $token  = $user->createToken('mobile')->plainTextToken;
        // // Mail::to($user->email)->send(new WelcomeEmail($user, $password));
        // return response()->json([
        //     'status' => true,
        //     'message' => 'User created successfully',
        //     'data' => ['token'=>$token,'user'=>new userResource($user->load('profile'))],
        // ]);
        return response()->json([
            'status' => true,
            'message' => 'User created successfully, OTP sent to email for verification',

        ]);
    }catch(\Exception $e){
            return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
        ], );
    }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {

       return new userResource(
            $user
        );

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        try {
            $data = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|string|email|max:255' ,
                'role' => 'sometimes|required|string',
            ]);

            $exists =  User::where('email',$data['email'])->exists();

           if($exists && $data['email'] != $user->email){
             return response()->json([
                'exists' => true,
                'message' => 'Email already in use',
            ]);
           };
            // Update user fields
            $user->update(
                [
                    'email' => $data['email'] ?? $user->email,
                    'name' => $data['name'] ?? $user->name,
                    'role' => $data['role'] ?? $user->role,
                ]
            );
            $user->profile()->update(
                [
                    'name' => $data['name'] ?? $user->profile->name,
                ]
            );


            return response()->json([
                'status' => true,
                'message' => 'User updated successfully',
                'data' => new userResource($user->load('profile'))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json([
            'message' => 'User deleted successfully',
        ], 200);
    }
    //search user by id
    public function searchUser($id){
        $user = User::where('id', '=', $id)->get();
        if($user->isEmpty()){
            return response()->json([
                'status' => false,
                'message' => 'No users found',
                'data' => null
            ]);
        }
        return response()->json([
                'status' => true,
                'message' => 'Users retrieved successfully',
                'data' => userResource::collection($user)
            ]);
    }
    }

