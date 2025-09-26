<?php
// app/Http/Controllers/Api/AuthController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AuthController extends Controller
{
    /**
     * WHY: Aligns with mobile payload and web form by normalizing inputs.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'            => ['required_without:first_name', 'string', 'max:255'],
            'first_name'      => ['required_without:name', 'string', 'max:255'],
            'last_name'       => ['nullable', 'string', 'max:255'],
            'username'        => ['nullable', 'string', 'max:255', Rule::unique('users', 'username')],
            'email'           => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'contact_number'  => ['required', 'string', 'max:20'],
            'address'         => ['nullable', 'string', 'max:1000'],
            'user_type'       => ['nullable', Rule::in(['Customer', 'Fixer', 'Admin', 'Support'])],
            'status'          => ['nullable', Rule::in(['Active', 'Inactive'])],
            'password'        => ['required', PasswordRule::defaults()],
        ]);

        // Normalize `name` → first/last
        [$fnFromName, $lnFromName] = $this->splitName($validated['name'] ?? '');
        $firstName = $validated['first_name'] ?? $fnFromName;
        $lastName  = $validated['last_name']  ?? $lnFromName;

        // Username generation if missing
        $username = $validated['username'] ?? $this->makeUniqueUsername(
            seed: trim($firstName . ' ' . ($lastName ?? '')),
            fallbackEmail: $validated['email']
        );

        $user = User::create([
            'first_name'     => $firstName,
            'last_name'      => $lastName,
            'username'       => $username,
            'email'          => $validated['email'],
            'contact_number' => $validated['contact_number'],
            'address'        => $validated['address'] ?? null,
            'user_type'      => $validated['user_type'] ?? 'Customer',
            'status'         => $validated['status'] ?? 'Active',
            'password'       => Hash::make($validated['password']),
        ]);

        event(new Registered($user)); // WHY: triggers any listeners (e.g., verification)

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'success' => true,
            'token'   => $token,
            'user'    => $user,
        ], 201);
    }

    /**
     * WHY: Avoids session-based Auth::attempt in stateless API.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'success' => true,
            'token'   => $token,
            'user'    => $user,
        ], 200);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'user'    => $request->user(),
        ], 200);
    }

    /**
     * Update the authenticated user's profile.
     * Accepts first_name/last_name or name (split), also camelCase variants.
     */
    public function updateMe(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        // Support multiple naming conventions from clients
        $input = [
            'first_name' => $request->input('first_name', $request->input('firstName')),
            'last_name'  => $request->input('last_name',  $request->input('lastName')),
            'name'       => $request->input('name',       $request->input('full_name')),
            'email'      => $request->input('email'),
        ];

        // If only `name` is provided, split into first/last
        if (($input['first_name'] === null || $input['first_name'] === '') &&
            ($input['last_name'] === null || $input['last_name'] === '') &&
            !empty($input['name'])) {
            [$fn, $ln] = $this->splitName($input['name']);
            $input['first_name'] = $fn;
            $input['last_name'] = $ln;
        }

        $validated = $request->validate([
            'first_name' => ['nullable', 'string', 'max:255'],
            'firstName'  => ['nullable', 'string', 'max:255'], // tolerated
            'last_name'  => ['nullable', 'string', 'max:255'],
            'lastName'   => ['nullable', 'string', 'max:255'],
            'name'       => ['nullable', 'string', 'max:255'],
            'full_name'  => ['nullable', 'string', 'max:255'],
            'email'      => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        // Apply updates only for provided fields
        if ($input['first_name'] !== null && $input['first_name'] !== '') {
            $user->first_name = $input['first_name'];
        } elseif (isset($validated['firstName'])) {
            $user->first_name = $validated['firstName'];
        }

        if ($input['last_name'] !== null && $input['last_name'] !== '') {
            $user->last_name = $input['last_name'];
        } elseif (isset($validated['lastName'])) {
            $user->last_name = $validated['lastName'];
        }

        if (!empty($validated['email'])) {
            $user->email = $validated['email'];
        }

        // If a consolidated `name` is sent and we still have gaps, fill them
        if (!empty($input['name'])) {
            [$fn, $ln] = $this->splitName($input['name']);
            if (!empty($fn) && empty($user->first_name)) $user->first_name = $fn;
            if ($ln !== null && empty($user->last_name)) $user->last_name = $ln;
        }

        $user->save();

        return response()->json([
            'success' => true,
            'user'    => $user->fresh(),
        ], 200);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken(); 
        if ($token) {
            $token->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out',
        ], 200);
    }

    private function splitName(string $name): array
    {
        $name = trim(preg_replace('/\s+/u', ' ', $name));
        if ($name === '') return ['', null];
        $parts = explode(' ', $name, 2);
        return [$parts[0], $parts[1] ?? null];
    }

    private function makeUniqueUsername(string $seed, string $fallbackEmail): string
    {
        $base = strtolower(preg_replace('/[^a-z0-9]+/i', '.', $seed));
        $base = trim($base, '.');
        if ($base === '') {
            $base = strtok($fallbackEmail, '@') ?: 'user';
        }

        $username = $base;
        $suffix = 0;

        while (User::where('username', $username)->exists()) {
            $suffix++;
            $username = $base . '.' . $suffix;
            if ($suffix > 1000) {
                $username = $base . '.' . bin2hex(random_bytes(2));
                break;
            }
        }

        return $username;
    }

    /**
     * Change the authenticated user's password.
     * Accepts: current_password, password, password_confirmation
     */
    public function changePassword(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'current_password'      => ['required', 'string'],
            'password'              => ['required', PasswordRule::defaults(), 'confirmed'],
            'password_confirmation' => ['required', 'string'],
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The provided password does not match your current password.'],
            ]);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully',
        ], 200);
    }
}
