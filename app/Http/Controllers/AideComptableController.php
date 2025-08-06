<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
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
        \Log::info('User creation request', [
            'request' => $request->all(),
        ]);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'avatarUrl' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'phoneNumber' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'zipCode' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                422
            ]);
        }

        try {
            $path = $request->file('avatarUrl')->store('uploads', 'public');
            $password = \Str::random(8); // Generate a random password
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phoneNumber' => $request->phoneNumber,
                'city' => $request->city,
                'state' => $request->state,
                'address' => $request->address,
                'zipCode' => $request->zipCode,
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
            $table = [
                'view' => 'emails.usercreated',
                'subject' => 'welcome to our platform..you can now login',
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'password' => $password,
                ],
            ];

            try {
                \Mail::to($user->email)->send(new UserCreated($table['view'], $table['subject'], $table['data'], null));
            } catch (\Exception $e) {
                // Handle the error
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
