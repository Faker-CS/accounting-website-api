<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\User;
use App\Mail\CredentialsMail;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Comptable (admin) creates users with a Spatie role.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'user' => $user,
            'accessToken' => $token,
        ]);
    }
    public function createUser(Request $request)
    {
        $current = Auth::user();
        if (!$current->hasRole('comptable')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            // Must match a role created via Spatie: 'aide-comptable' or 'entreprise'
            'role' => 'required|exists:roles,name',
        ]);

        $password = Str::random(10);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($password),
        ]);

        // Assign the chosen role
        $user->assignRole($request->role);

        // Send email credentials
        Mail::to($user->email)->send(new CredentialsMail($user, $password));

        return response()->json([
            'message' => 'User created, role assigned, and credentials emailed.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->getRoleNames()->first(), // Gets the first role name
            ],

        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials...'], 401);
        }
        $user = Auth::user();

        $roles = $user->roles ? $user->roles->pluck('name')->toArray() : [];
        if($user->hasRole('entreprise')) {
            $company = $user->company();
        }
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phoneNumber' => $user->phoneNumber,
                'city' => $user->city,
                'state' => $user->state,
                'address' => $user->address,
                'zipCode' => $user->zipCode,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'roles' => $roles, // Only role names, e.g., ["aide-comptable"]
                'photo' => $user->photo,
                'company_id' => $user->company_id,
            ],
            'accessToken' => $token,
        ]);
    }

    public function me(){
        return response()->json(auth()->user());
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function user()
    {
        $user = Auth::user();
        $roleName = $user->roles->pluck('name')->first();
        return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phoneNumber' => $user->phoneNumber,
                'city' => $user->city,
                'state' => $user->state,
                'address' => $user->address,
                'zipCode' => $user->zipCode,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'roles' => $roleName,
                'photo' => $user->photo,
                'company_id' => $user->company_id,
            ],);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);
        $user = \App\Models\User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $password = \Str::random(10);
        $user->password = \Hash::make($password);
        $user->save();
        try {
            \Mail::to($user->email)->send(new \App\Mail\CredentialsMail($user, $password));
        } catch (\Exception $e) {
            // Handle the error
        }
        return response()->json(['message' => 'Password reset and sent by email.']);
    }
}
