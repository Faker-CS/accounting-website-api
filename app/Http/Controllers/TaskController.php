<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->hasRole('aide-comptable')) {
            $tasks = Task::where('assignee_id', $user->id)
                        ->with(['form', 'reporter', 'assignee', 'subtasks', 'form.helperForms.user'])
                        ->get();
        } else {
            $tasks = Task::with(['form', 'reporter', 'assignee', 'subtasks', 'form.helperForms.user'])->get();
        }
        
        // Group tasks by status
        $groupedTasks = $tasks->groupBy('status');

        // Ensure all columns exist in tasks
        $columns = ['To Do', 'In Progress', 'Ready to Check', 'Done'];
        $tasksByColumn = [];
        foreach ($columns as $col) {
            $tasksByColumn[$col] = $groupedTasks[$col] ?? collect();
        }

        $board = [
            'columns' => collect($columns)->map(fn($col) => ['id' => $col, 'name' => $col])->toArray(),
            'tasks' => collect($tasksByColumn)->map(fn($tasks) => $tasks->values())->toArray()
        ];
        
        return response()->json(['board' => $board]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'form_id' => 'required|exists:forms,id',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'assignee_id' => 'required|exists:users,id',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:Low,Medium,High'
        ]);

        $form = Form::with('user')->findOrFail($request->form_id);
        
        $task = Task::create([
            'form_id' => $request->form_id,
            'title' => $request->title,
            'description' => $request->description,
            'reporter_id' => $form->user->id,
            'assignee_id' => $request->assignee_id,
            'due_date' => $request->due_date,
            'priority' => $request->priority,
            'status' => 'To Do'
        ]);

        return response()->json($task->load(['form', 'reporter', 'assignee', 'subtasks', 'form.helperForms.user']));
    }

    /**
     * Display the specified resource.
     */
    // public function show(Task $task)
    // {
    //     //
    // }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        
        $request->validate([
            'title' => 'sometimes|string',
            'description' => 'nullable|string',
            'assignee_id' => 'sometimes|exists:users,id',
            'due_date' => 'nullable|date',
            'priority' => 'sometimes|in:Low,Medium,High',
            'status' => 'sometimes|in:To Do,In Progress,Ready to Check,Done'
        ]);

        $task->update($request->all());
        
        // Check if task status was updated to "Done"
        if ($request->has('status') && $request->status === 'Done') {
            $this->checkAndUpdateFormStatus($task->form_id);
        }
        
        return response()->json($task->load(['form', 'reporter', 'assignee', 'subtasks', 'form.helperForms.user']));
    }

    /**
     * Check if all tasks for a form are done and update form status accordingly
     */
    private function checkAndUpdateFormStatus($formId)
    {
        $form = Form::with('tasks')->findOrFail($formId);
        
        // Get all tasks for this form
        $allTasks = $form->tasks;
        
        // Check if all tasks have status "Done"
        $allTasksDone = $allTasks->isNotEmpty() && $allTasks->every(function ($task) {
            return $task->status === 'Done';
        });
        
        // If all tasks are done, update form status to "done"
        if ($allTasksDone) {
            $form->update(['status' => 'done']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();
        
        return response()->json(['message' => 'Task deleted successfully']);
    }
}
