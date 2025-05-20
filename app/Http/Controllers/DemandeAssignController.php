<?php

namespace App\Http\Controllers;

use App\Models\HelperForms;
use Illuminate\Http\Request;
use App\Models\Form;
use App\Models\User;
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

        // $demande = Form::findorfail($demandId);
        $assign = HelperForms::where('form_id', $demandId)
            ->where('user_id', $request->userId)
            ->first();
        if ($assign) {
            $assign->delete();
            return response()->json([
                'message' => 'Helper removed from demande successfully',
            ], status: 200);
        } else {
            HelperForms::create([
                'user_id' => $request->userId,
                'form_id' => $demandId,
            ]);

            // Get the helper user
            $helper = User::findOrFail($request->userId);
            
            // Get the current user (comptable) who is making the assignment
            $comptable = auth()->user();
            
            // Send notification to the helper
            $helper->notify(new HelperAssignedNotification($demandId, $comptable->name));

            return response()->json([
                'message' => 'Helper assigned to demande successfully',
            ]);
        }
    }
}
