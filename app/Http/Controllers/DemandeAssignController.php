<?php

namespace App\Http\Controllers;

use App\Models\HelperForms;
use Illuminate\Http\Request;
use App\Models\Form;
use App\Models\User;
use App\Models\Task;
use App\Models\Notification;
use App\Events\FormSubmitted;
use App\Notifications\HelperAssignedNotification;
use Illuminate\Support\Facades\Log;

class DemandeAssignController extends Controller
{
    public function AssignHelperToDemande(Request $request, $demandId)
    {
        Log::info("AssignHelperToDemande called", [
            'demandId' => $demandId,
            'userId' => $request->userId,
        ]);
        $request->validate([
            'userId' => 'required|integer|exists:users,id',
        ]);

        $demande = Form::with('service')->findOrFail($demandId);

        // Update or create task
        $task = Task::where('form_id', $demandId)->first();
        if ($task) {
            $task->update([
                'assignee_id' => $request->userId,
                'status' => 'In Progress'
            ]);
        } else {
            // Create task if it doesn't exist
            Task::create([
                'form_id' => $demandId,
                'title' => $demande->service->name,
                'description' => "New demand for {$demande->service->name}",
                'reporter_id' => $demande->user_id,
                'assignee_id' => $request->userId,
                'due_date' => now()->addDays(7),
                'priority' => 'Medium',
                'status' => 'In Progress'
            ]);
        }
        
        $assign = HelperForms::where('form_id', $demandId)
            ->where('user_id', $request->userId)
            ->first();
            
        if ($assign) {
            $assign->delete();
            return response()->json([
                'message' => 'Helper removed from demande successfully',
            ], status: 200);
        }
        
        HelperForms::create([
            'user_id' => $request->userId,
            'form_id' => $demandId,
        ]);

        // Get the helper user
        $helper = User::findOrFail($request->userId);
        
        // Get the current user (comptable) who is making the assignment
        $comptable = auth()->user();
        
        // Change status to in_work if it's not already
        if ($demande->status !== 'in_work') {
            $demande->status = 'in_work';
            $demande->save();

            // Notify the company about the status change
            $company = $demande->user;
            if ($company) {
                Notification::create([
                    'user_id' => $company->id,
                    'type' => 'form_in_work',
                    'title' => "Your form for {$demande->service->name} is now being processed by our team.",
                    'serviceLink' => "/dashboard/forms/{$demandId}",
                    'isUnRead' => true,
                ]);
                broadcast(new FormSubmitted([
                    'title' => "Your form for {$demande->service->name} is now being processed by our team.",
                    'type' => 'form_in_work',
                    'link' => "/dashboard/forms/{$demandId}"
                ], $company->id));
            }
        }
        
        // Send notification to the helper
        $helper->notify(new HelperAssignedNotification($demandId, $comptable->name));

        // Create notification in database for the aide-comptable
        $notification = Notification::create([
            'user_id' => $helper->id,
            'type' => 'helper_assigned',
            'title' => "You have been assigned a new request : <strong>{$demande->service->name}</strong> par {$comptable->name}",
            'serviceLink' => "/dashboard/forms/{$demandId}",
            'isUnRead' => true,
        ]);

        // Broadcast the event to the aide-comptable
        broadcast(new FormSubmitted([
            'title' => "You have been assigned a new request : <strong>{$demande->service->name}</strong> par {$comptable->name}",
            'type' => 'helper_assigned',
            'link' => "/dashboard/forms/{$demandId}"
        ], $helper->id));

        return response()->json([
            'message' => 'Helper assigned to demande successfully',
        ]);
    }
}
