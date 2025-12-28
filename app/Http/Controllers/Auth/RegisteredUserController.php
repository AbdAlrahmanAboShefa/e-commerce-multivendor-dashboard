<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            \Log::info('Registration attempt', [
                'email' => $validated['email'],
                'name' => $validated['name']
            ]);

            // Use database transaction to ensure data integrity
            $user = \DB::transaction(function () use ($validated) {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => $validated['password'], // Will be automatically hashed by the 'hashed' cast
                ]);

                // Verify user was created
                if (!$user || !$user->id) {
                    throw new \Exception('Failed to create user record');
                }

                \Log::info('User created successfully', [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);
                $user->assignRole('user');

    
                return $user;
            });

            // Fire registered event
            event(new Registered($user));

            // Create API token
            $token = $user->createToken('auth-token')->plainTextToken;

            \Log::info('Registration successful', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return response()->json([
                
                'message' => 'User registered successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                ],
                'token' => $token,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('Registration validation failed', [
                'errors' => $e->errors()
            ]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Registration error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Registration failed',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during registration. Please try again.'
            ], 500);
        }
    }
}
