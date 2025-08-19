<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Form;
use App\Models\SubtaskTemplate;
use App\Models\Subtask;
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
                        ->orWhereHas('assignees', function($query) use ($user) {
                            $query->where('user_id', $user->id);
                        })
                        ->orWhereHas('form.helperForms', function($query) use ($user) {
                            $query->where('user_id', $user->id);
                        })
                        ->with(['form', 'service', 'reporter', 'assignee', 'assignees', 'subtasks', 'form.helperForms.user'])
                        ->get();
        } else {
            $tasks = Task::with(['form', 'service', 'reporter', 'assignee', 'assignees', 'subtasks', 'form.helperForms.user'])->get();
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
            'form_id' => 'nullable|exists:forms,id',
            'service_id' => 'nullable|exists:services,id',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'assignee_id' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:Low,Medium,High',
            'status' => 'required|in:To Do,In Progress,Ready to Check,Done'
        ]);

        $user = Auth::user();
        
        // Determine reporter based on context
        if ($request->form_id) {
            // For form-based tasks, get the company of the user who submitted the form
            $form = Form::with('user.company')->findOrFail($request->form_id);
            $reporterId = $form->user->company_id;
        } else {
            // For service-based tasks, use the comptable's company as reporter
            $reporterId = $user->company_id;
        }
        
        // If no company is associated, set reporter as null
        if (!$reporterId) {
            $reporterId = null;
        }
        
        $task = Task::create([
            'form_id' => $request->form_id,
            'service_id' => $request->service_id,
            'title' => $request->title,
            'description' => $request->description,
            'reporter_id' => $reporterId,
            'assignee_id' => $request->assignee_id,
            'due_date' => $request->due_date,
            'priority' => $request->priority,
            'status' => $request->status ?? 'To Do'
        ]);

        return response()->json($task->load(['form', 'service', 'reporter', 'assignee', 'assignees', 'subtasks', 'form.helperForms.user']));
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
        
        // Debug: Log the incoming request data
        \Log::info('Task update request for task ' . $id, $request->all());
        
        $request->validate([
            'title' => 'sometimes|string',
            'description' => 'nullable|string',
            'assignee_id' => 'sometimes|nullable|exists:users,id',  // Made optional and nullable for legacy support
            'assignee_ids' => 'sometimes|array',
            'assignee_ids.*' => 'exists:users,id',
            'reporter_id' => 'sometimes|exists:companies,id',
            'due_date' => 'nullable|date',
            'priority' => 'sometimes|in:Low,Medium,High',
            'status' => 'sometimes|in:To Do,In Progress,Ready to Check,Done'
        ]);

        $task->update($request->except(['assignee_ids']));
        
        // Handle multiple assignees
        if ($request->has('assignee_ids')) {
            $assigneeIds = $request->assignee_ids;
            $syncData = [];
            foreach ($assigneeIds as $userId) {
                $syncData[$userId] = ['assigned_at' => now()];
            }
            $task->assignees()->sync($syncData);
        }
        
        // Check if task status was updated to "Done"
        if ($request->has('status') && $request->status === 'Done') {
            $this->checkAndUpdateFormStatus($task->form_id);
        }
        
        return response()->json($task->load(['form', 'service', 'reporter', 'assignee', 'assignees', 'subtasks', 'form.helperForms.user']));
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
        $user = Auth::user();
        
        // Only comptable role can delete tasks
        if (!$user->hasRole('comptable')) {
            return response()->json(['error' => 'Unauthorized. Only comptable users can delete tasks.'], 403);
        }
        
        $task = Task::findOrFail($id);
        $task->delete();
        
        return response()->json(['message' => 'Task deleted successfully']);
    }
}
