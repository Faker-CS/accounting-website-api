<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();
        return response()->json($user);
    }

    public function update(Request $request)
    {
        $user = \Auth::user();
        \Log::info('Updating user profile',$request->all());
        \Log::info(' user profile',[$user]);
        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phoneNumber' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'zipCode' => 'nullable|string|max:20',
            'currentPassword' => 'nullable|required_with:newPassword|string|min:6',
            'newPassword' => 'nullable|string|min:6|confirmed',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Update basic info
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        
        // Only update optional fields if they are provided
        if (isset($validated['phoneNumber'])) {
            $user->phoneNumber = $validated['phoneNumber'];
        }
        if (isset($validated['city'])) {
            $user->city = $validated['city'];
        }
        if (isset($validated['state'])) {
            $user->state = $validated['state'];
        }
        if (isset($validated['address'])) {
            $user->address = $validated['address'];
        }
        if (isset($validated['zipCode'])) {
            $user->zipCode = $validated['zipCode'];
        }

        // Handle password update if provided
        if ($request->filled('currentPassword') && $request->filled('newPassword')) {
            if (!Hash::check($request->currentPassword, $user->password)) {
                return response()->json(['message' => 'Current password is incorrect'], 422);
            }
            $user->password = Hash::make($request->newPassword);
        }

        // Handle photo upload if provided
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            // Store new photo in 'uploads'
            $path = $request->file('photo')->store('uploads', 'public');
            $user->photo = $path;
        }

        try {
            $user->save();
            
            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            Log::error('Profile update error:', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            
            return response()->json([
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
