<?php

namespace App\Http\Controllers\v1;
use App\Http\Controllers\v1\Controller;
use App\Models\Plant;
use App\Http\Requests\StoreplantRequest;
use App\Http\Requests\UpdateplantRequest;
use App\Http\Resources\v1\plantResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlantController extends Controller
{
/**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = request()->user();
        return plantResource::collection($user->plants()->latest()->get());
    }

    public function recentScan(Request $request){
           $user = request()->user();
         return plantResource::collection($user->plants()->latest()->take(3)->get());
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
        $image_uploaded_path = null;

        try{
            $data = $request->validate([
            'diseaseId' => 'sometimes|integer',
            'image' =>'sometimes|nullable|image',
            'confidence' => 'sometimes|nullable|numeric'
        ]);
         if ($request->hasFile('image') || $request->file('image')!= null ) {
        $uploadFolder = 'plants';
        $image = $request->file('image');

        $image_name = time() . '_' . $image->getClientOriginalName();


        $image_uploaded_path = $image->storeAs($uploadFolder , $image_name, 's3');
    //    $image_uploaded_path = $request->file('image')->storePubliclyAs($uploadFolder , $image_name, 's3');


       }
        $plant = Plant::create([
            'diseaseId' => $data['diseaseId'],
            'image' => $image_uploaded_path,
            'user_id' => $request->user()->id,
            'confidence' => $data['confidence'],
        ]);
            return response()->json(['status' => true,'message' => 'Plant created successfully','data' =>new plantResource($plant)], 201);
    } catch (\Exception $e) {
            return response()->json(['status' => false,'message' => 'Plant not created successfully', 'error' => $e->getMessage()], 500);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(Plant $plant)
    {
        return new plantResource($plant);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Plant $plant)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateplantRequest $request, Plant $plant)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Plant $plant)
    {
        try {
            $plant->delete();
            return response()->json(['status' => true, 'message' => 'Plant deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Plant not deleted', 'error' => $e->getMessage()], 500);
        }
    }
}
