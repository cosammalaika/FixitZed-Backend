<?php
// app/Http/Controllers/Api/AuthController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Province;
use App\Models\User;
use App\Support\ProvinceDistrict;
use App\Notifications\ResetPasswordOtp;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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
            'province_id'     => ['nullable', 'integer', Rule::exists('provinces', 'id')],
            'province_slug'   => ['nullable', 'string', 'max:255'],
            'province'        => ['nullable', 'string', 'max:255'],
            'district_id'     => ['nullable', 'integer', Rule::exists('districts', 'id')],
            'district_slug'   => ['nullable', 'string', 'max:255'],
            'district'        => ['nullable', 'string', 'max:255'],
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

        $province = $this->resolveProvince($request);
        if (! $province) {
            throw ValidationException::withMessages([
                'province' => ['Please select a valid province.'],
            ]);
        }

        $district = $this->resolveDistrict($request, $province);
        if (! $district) {
            throw ValidationException::withMessages([
                'district' => ['Please select a valid district for the selected province.'],
            ]);
        }

        $address = $this->composeAddress($province->name, $district->name, $validated['address'] ?? null);

        $user = User::create([
            'first_name'     => $firstName,
            'last_name'      => $lastName,
            'username'       => $username,
            'email'          => $validated['email'],
            'contact_number' => $validated['contact_number'],
            'province'       => $province->name,
            'district'       => $district->name,
            'address'        => $address,
            'status'         => $validated['status'] ?? 'Active',
            'password'       => Hash::make($validated['password']),
        ]);

        $roleFromRequest = $validated['user_type'] ?? null;
        $roles = ['Customer'];
        if ($roleFromRequest && ! in_array($roleFromRequest, $roles, true)) {
            $roles[] = $roleFromRequest;
        }
        $user->syncRoles($roles);

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
        $validated = $request->validate([
            'identifier' => ['required_without:email', 'string'],
            'email'      => ['nullable', 'email'],
            'password'   => ['required', 'string'],
        ]);

        $identifier = $validated['identifier'] ?? $validated['email'];
        $user = $identifier ? $this->findUserByIdentifier($identifier) : null;

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'identifier' => ['The provided credentials are incorrect.'],
                'email'      => ['The provided credentials are incorrect.'],
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
        $user = $request->user()->loadMissing('roles');
        return response()->json([
            'success' => true,
            'user'    => $user,
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
            'first_name'    => ['nullable', 'string', 'max:255'],
            'firstName'     => ['nullable', 'string', 'max:255'], // tolerated
            'last_name'     => ['nullable', 'string', 'max:255'],
            'lastName'      => ['nullable', 'string', 'max:255'],
            'name'          => ['nullable', 'string', 'max:255'],
            'full_name'     => ['nullable', 'string', 'max:255'],
            'email'         => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'address'       => ['nullable', 'string', 'max:1000'],
            'province_id'   => ['nullable', 'integer', Rule::exists('provinces', 'id')],
            'province_slug' => ['nullable', 'string', 'max:255'],
            'province'      => ['nullable', 'string', 'max:255'],
            'district_id'   => ['nullable', 'integer', Rule::exists('districts', 'id')],
            'district_slug' => ['nullable', 'string', 'max:255'],
            'district'      => ['nullable', 'string', 'max:255'],
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

        if ($this->hasProvinceInput($request)) {
            $province = $this->resolveProvince($request);
            if (! $province) {
                throw ValidationException::withMessages([
                    'province' => ['Please select a valid province.'],
                ]);
            }

            $district = $this->resolveDistrict($request, $province);
            if (! $district) {
                throw ValidationException::withMessages([
                    'district' => ['Please select a valid district for the selected province.'],
                ]);
            }

            $user->province = $province->name;
            $user->district = $district->name;
            $user->address = $this->composeAddress($province->name, $district->name, $validated['address'] ?? null);
        } elseif (array_key_exists('address', $validated) && ($user->province || $user->district)) {
            $user->address = $this->composeAddress($user->province, $user->district, $validated['address']);
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

    public function forgotPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'identifier' => ['required_without:email', 'string'],
            'email' => ['nullable', 'email'],
        ]);

        $identifier = $validated['identifier'] ?? $validated['email'] ?? '';
        $user = $identifier !== '' ? $this->findUserByIdentifier($identifier) : null;

        if (! $user) {
            return response()->json([
                'success' => true,
                'message' => 'If we find a matching account, a reset code will be emailed shortly.',
            ], 200);
        }

        if (empty($user->email)) {
            return response()->json([
                'success' => false,
                'message' => 'This account does not have an email address on file. Please contact support to reset your password.',
            ], 422);
        }

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($otp),
                'created_at' => now(),
            ]
        );

        try {
            $user->notify(new ResetPasswordOtp($otp));
        } catch (\Throwable $e) {
            Log::warning('Failed to send password reset code', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'If we find a matching account, a reset code will be emailed shortly.',
        ], 200);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'identifier' => ['required_without:email', 'string'],
            'email' => ['nullable', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', PasswordRule::defaults(), 'confirmed'],
            'password_confirmation' => ['required', 'string'],
        ]);

        $identifier = $validated['identifier'] ?? $validated['email'] ?? '';
        $user = $identifier !== '' ? $this->findUserByIdentifier($identifier) : null;

        if (! $user || empty($user->email)) {
            throw ValidationException::withMessages([
                'identifier' => ['We could not find an account that matches that information.'],
            ]);
        }

        $record = DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->first();

        if (! $record) {
            throw ValidationException::withMessages([
                'token' => ['The reset code is invalid or has already been used.'],
            ]);
        }

        $createdAt = $record->created_at ? Carbon::parse($record->created_at) : null;
        if (! $createdAt || $createdAt->addMinutes(15)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();

            throw ValidationException::withMessages([
                'token' => ['The reset code has expired. Please request a new one.'],
            ]);
        }

        if (! Hash::check($validated['token'], $record->token)) {
            throw ValidationException::withMessages([
                'token' => ['The reset code is incorrect. Please try again.'],
            ]);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $user->email)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully. You can now sign in with your new password.',
        ], 200);
    }

    private function splitName(string $name): array
    {
        $name = trim(preg_replace('/\s+/u', ' ', $name));
        if ($name === '') return ['', null];
        $parts = explode(' ', $name, 2);
        return [$parts[0], $parts[1] ?? null];
    }

    private function findUserByIdentifier(string $identifier): ?User
    {
        $identifier = trim($identifier);
        if ($identifier === '') {
            return null;
        }

        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            return User::where('email', $identifier)->first();
        }

        $normalized = $this->normalizePhone($identifier);

        return User::where(function ($query) use ($identifier, $normalized) {
            $query->where('contact_number', $identifier);

            if ($normalized !== null) {
                $query->orWhere('contact_number', $normalized)
                      ->orWhere('contact_number', '+' . $normalized)
                      ->orWhereRaw(
                          "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(contact_number, ' ', ''), '-', ''), '(', ''), ')', ''), '+', '') = ?",
                          [$normalized]
                      );
            }
        })->first();
    }

    private function hasProvinceInput(Request $request): bool
    {
        return $request->filled('province_id')
            || $request->filled('province_slug')
            || $request->filled('province')
            || $request->filled('district_id')
            || $request->filled('district_slug')
            || $request->filled('district');
    }

    private function resolveProvince(Request $request): ?Province
    {
        $id = $request->input('province_id');
        if ($id !== null) {
            $province = Province::find($id);
            if ($province) {
                return $province;
            }
        }

        $slug = trim((string) $request->input('province_slug', ''));
        if ($slug !== '') {
            $province = Province::where('slug', Str::slug($slug))->first();
            if ($province) {
                return $province;
            }
        }

        $name = trim((string) $request->input('province', ''));
        if ($name !== '') {
            $province = Province::whereRaw('LOWER(name) = ?', [Str::lower($name)])->first();
            if ($province) {
                return $province;
            }

            return $this->ensureProvinceFromConfig($name);
        }

        return null;
    }

    private function resolveDistrict(Request $request, Province $province): ?District
    {
        $id = $request->input('district_id');
        if ($id !== null) {
            $district = District::where('province_id', $province->id)
                ->where('id', $id)
                ->first();
            if ($district) {
                return $district;
            }
        }

        $slug = trim((string) $request->input('district_slug', ''));
        if ($slug !== '') {
            $district = District::where('province_id', $province->id)
                ->where('slug', Str::slug($slug))
                ->first();
            if ($district) {
                return $district;
            }
        }

        $name = trim((string) $request->input('district', ''));
        if ($name !== '') {
            $district = District::where('province_id', $province->id)
                ->whereRaw('LOWER(name) = ?', [Str::lower($name)])
                ->first();
            if ($district) {
                return $district;
            }

            return $this->ensureDistrictFromConfig($province, $name);
        }

        return null;
    }

    private function ensureProvinceFromConfig(string $candidate): ?Province
    {
        $map = config('provinces.map', []);
        if (! is_array($map)) {
            return null;
        }

        foreach ($map as $provinceName => $districts) {
            if (! is_string($provinceName)) {
                continue;
            }

            if (Str::lower($provinceName) === Str::lower($candidate)) {
                $province = Province::firstOrCreate(
                    ['slug' => Str::slug($provinceName)],
                    ['name' => $provinceName]
                );

                foreach ((array) $districts as $districtName) {
                    if (! is_string($districtName)) {
                        continue;
                    }
                    $districtName = trim($districtName);
                    if ($districtName === '') {
                        continue;
                    }
                    District::firstOrCreate(
                        [
                            'province_id' => $province->id,
                            'slug' => Str::slug($districtName),
                        ],
                        ['name' => $districtName]
                    );
                }

                ProvinceDistrict::refresh();

                return $province->fresh();
            }
        }

        return null;
    }

    private function ensureDistrictFromConfig(Province $province, string $candidate): ?District
    {
        $map = config('provinces.map', []);
        if (! is_array($map)) {
            return null;
        }

        foreach ($map as $provinceName => $districts) {
            if (! is_string($provinceName)) {
                continue;
            }
            if (Str::lower($provinceName) !== Str::lower($province->name)) {
                continue;
            }

            foreach ((array) $districts as $districtName) {
                if (! is_string($districtName)) {
                    continue;
                }

                if (Str::lower($districtName) === Str::lower($candidate)) {
                    $district = District::firstOrCreate(
                        [
                            'province_id' => $province->id,
                            'slug' => Str::slug($districtName),
                        ],
                        ['name' => $districtName]
                    );

                    ProvinceDistrict::refresh();

                    return $district;
                }
            }
        }

        return null;
    }

    private function composeAddress(?string $provinceName, ?string $districtName, ?string $extra = null): ?string
    {
        $parts = [];
        if ($provinceName !== null && trim($provinceName) !== '') {
            $parts[] = trim($provinceName);
        }
        if ($districtName !== null && trim($districtName) !== '') {
            $parts[] = trim($districtName);
        }

        $location = implode(', ', $parts);
        $extra = $extra !== null ? trim($extra) : null;

        if ($location === '' && ($extra === null || $extra === '')) {
            return null;
        }

        if ($location === '') {
            return $extra;
        }

        if ($extra !== null && $extra !== '') {
            return $location . ' - ' . $extra;
        }

        return $location;
    }

    private function normalizePhone(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value);

        return $digits !== '' ? $digits : null;
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
