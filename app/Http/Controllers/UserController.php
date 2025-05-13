<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();
        \Log::info('User profile accessed', [
            'user_id' => $user->id,
            'email' => $user->email,
            'password' => $user->Hash::check($user->password),
        ]);
        return response()->json([
            $request->user(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();

        $request ->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phoneNumber' => 'nullable|string|max:255',
        ]);
        $user -> email = $request -> email;

        if($request ->filled('password')) {
            $user -> password = \Hash::make($request -> password);
        }
        $user -> save();
        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user,
        ]);
    }
}
