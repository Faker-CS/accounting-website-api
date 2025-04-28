<?php

namespace App\Http\Controllers;

use App\Models\Industry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class IndustryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'data' => Industry::paginate(10),
            'message' => 'Industries retrieved successfully'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:industries'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $industry = Industry::create($validator->validated());
        return response()->json([
            'data' => $industry,
            'message' => 'Industry created successfully'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Industry $industry)
    {
        return response()->json([
            'data' => $industry,
            'message' => 'Industry retrieved successfully'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Industry $industry)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:industries,name,'.$industry->id
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $industry->update($validator->validated());
        return response()->json([
            'data' => $industry,
            'message' => 'Industry updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Industry $industry)
    {
        $industry->delete();
        return response()->json(['message' => 'Industry deleted successfully']);
    }
}