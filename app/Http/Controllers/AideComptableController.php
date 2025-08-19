<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\UserCreated;


class AideComptableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = User::role("aide-comptable")->get();
        return response()->json($user);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info('User creation request', [
            'request' => $request->all(),
        ]);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'avatarUrl' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'phoneNumber' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                422
            ]);
        }

        try {
            $path = null;
            if ($request->hasFile('avatarUrl')) {
                $path = $request->file('avatarUrl')->store('uploads', 'public');
            }
            
            $password = \Str::random(8); // Generate a random password
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phoneNumber' => $request->phoneNumber,
                'address' => $request->address,
                'password' => \Hash::make($password),
                'photo' => $path,
            ]);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not created',
                ], 500);
            }

            // Assign the role to the user
            $user->assignRole('aide-comptable');

            // send the user an email
            try {
                $mailData = [
                    'name' => $user->name,
                    'email' => $user->email,
                    'password' => $password,
                ];

                Mail::to($user->email)->send(new UserCreated(
                    'emails.usercreated',
                    'Bienvenue sur notre plateforme - Vous pouvez maintenant vous connecter',
                    $mailData,
                    null
                ));

                Log::info('Welcome email sent successfully', [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send welcome email', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage()
                ]);
                // Continue execution even if email fails
            }
            return response()->json([
                'email' => $user->email,
                'password' => $password,
                'name' => $user->name
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not created',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = User::role("aide-comptable")
            ->with(['helperForms.form.user.company', 'helperForms.form.service'])
            ->findorfail($id);
            
        if ($user) {
            return $user;
        } else {
            return response()->json(['message' => 'User not found'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::role('aide-comptable')->findorfail($id);

        if ($user) {
            $user->update($request->all());
            return response()->json(['message' => 'User updated successfully']);
        } else {
            return response()->json(['message' => 'User not found'], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::role("aide-comptable")->findorfail($id);
        if ($user) {
            $user->delete();
            return response()->json(['message' => 'User deleted successfully']);
        } else {
            return response()->json(['message' => 'User not found'], 404);
        }
    }
}
