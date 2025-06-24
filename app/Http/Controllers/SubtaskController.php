<?php

namespace App\Http\Controllers;

use App\Models\Subtask;
use App\Models\Task;
use Illuminate\Http\Request;

class SubtaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     //
    // }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $taskId)
    {
        $request->validate([
            'title' => 'required|string'
        ]);

        $task = Task::findOrFail($taskId);
        
        $subtask = $task->subtasks()->create([
            'title' => $request->title,
            'is_completed' => false
        ]);

        return response()->json($subtask);
    }

    /**
     * Display the specified resource.
     */
    // public function show(Subtask $subtask)
    // {
    //     //
    // }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $subtask = Subtask::findOrFail($id);
        
        $request->validate([
            'title' => 'sometimes|string',
            'is_completed' => 'sometimes|boolean'
        ]);

        $subtask->update($request->all());
        
        return response()->json($subtask);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $subtask = Subtask::findOrFail($id);
        $subtask->delete();
        
        return response()->json(['message' => 'Subtask deleted successfully']);
    }
}
