<?php

namespace App\Http\Controllers;

use App\Models\Subtask;
use App\Models\SubtaskTemplate;
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

        $task = Task::with('service')->findOrFail($taskId);
        
        $subtask = $task->subtasks()->create([
            'title' => $request->title,
            'is_completed' => false
        ]);

        // If this task is associated with a service, check if we should save this as a template
        if ($task->service_id) {
            // Check if this subtask title already exists as a template for this service
            $existingTemplate = SubtaskTemplate::where('service_id', $task->service_id)
                ->where('title', $request->title)
                ->first();

            // If no existing template, create one
            if (!$existingTemplate) {
                $maxOrder = SubtaskTemplate::where('service_id', $task->service_id)
                    ->max('order');
                
                SubtaskTemplate::create([
                    'service_id' => $task->service_id,
                    'title' => $request->title,
                    'order' => ($maxOrder ?? -1) + 1
                ]);
            }
        }

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
