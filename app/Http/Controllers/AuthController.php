<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
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

        // First check if user exists and credentials are valid
        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials...'], 401);
        }
        
        $user = Auth::user();

        // Check if user is blocked before creating token
        if ($user->is_blocked) {
            // Log out the user immediately
            Auth::logout();
            
            // Determine language from Accept-Language header
            $acceptLanguage = $request->header('Accept-Language', 'en');
            $isFrench = str_contains($acceptLanguage, 'fr');
            
            $message = $isFrench 
                ? 'Votre compte a été bloqué par le comptable. Veuillez contacter l\'administrateur.'
                : 'Your account has been blocked by the accountant. Please contact the administrator.';
                
            return response()->json(['message' => $message], 403);
        }

        // Now create the JWT token
        $token = JWTAuth::fromUser($user);

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
        
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Generate new password
        $newPassword = Str::random(12);
        
        // Update user password
        $user->password = Hash::make($newPassword);
        $user->save();

        try {
            Mail::to($user->email)->send(new CredentialsMail($user, $newPassword));

            Log::info('Password reset email sent successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send password reset email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Failed to send reset email'], 500);
        }

        return response()->json([
            'message' => 'Password reset successfully. Check your email for the new password.',
            'success' => true
        ]);
    }
}
