<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use App\Traits\ResponseHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rules\Password;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    use ResponseHandler;

    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:255'],
            'google_id' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['nullable', 'string'],
        ]);

        $validator->sometimes('password', ['required'], function ($input) {
            return $input->type === 'email';
        });

        $validator->sometimes('google_id', ['required'], function ($input) {
            return $input->type === 'google';
        });

        if ($validator->fails()) {
            return $this->error(422, $validator->errors()->first(), [], false);
        }

        $validatedData = $validator->validated();
        $data = array_map('trim', $validatedData);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => isset($data['password']) ? Hash::make($data['password']) : null,
            'google_id' => $data['google_id'] ?? null,
        ]);

        // Create or get 'member' role and assign it
        $memberRole = Role::firstOrCreate(['name' => 'member']);
        $user->assignRole($memberRole);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success(Response::HTTP_CREATED, 'User registered successfully.', [
            'user' => $this->formatUser($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'nullable|string',
            'type' => 'required|string|in:email,google',
            'google_id' => 'nullable|string',
        ]);

        $validator->sometimes('google_id', 'required', function ($input) {
            return $input->type === 'google';
        });

        $validator->sometimes('password', 'required', function ($input) {
            return $input->type === 'email';
        });

        if ($validator->fails()) {
            return $this->error(422, $validator->errors()->first(), [], false);
        }

        $credentials = $validator->validated();

        if ($request->type === 'email') {
            $emailCreds = Arr::only($credentials, ['email', 'password']);
            if (!Auth::attempt($emailCreds)) {
                return $this->error(Response::HTTP_UNAUTHORIZED, 'The provided credentials are incorrect.', [], false);
            }
            $user = Auth::user();
        } else if ($request->type === 'google') {
            $user = User::where('email', $credentials['email'])->first();

            if (!$user) {
                return $this->error(Response::HTTP_UNAUTHORIZED, 'User not found. Please register first.', [], false);
            }

            if (!$user->google_id) {
                return $this->error(Response::HTTP_UNAUTHORIZED, 'This email is not registered with Google login.', [], false);
            }

            if ($user->google_id !== $credentials['google_id']) {
                return $this->error(Response::HTTP_UNAUTHORIZED, 'Invalid Google login credentials.', [], false);
            }

            Auth::login($user);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        if ($request->filled('fcm_token')) {
            $this->storeFcmToken($request, $user);
        }

        return $this->success(Response::HTTP_OK, 'User logged in successfully.', [
            'user' => $this->formatUser($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->bearerToken();

        if (!$token) {
            return $this->error(400, 'No token provided.', [], false);
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return $this->error(400, 'No active token found for the user.', [], false);
        }

        if ($accessToken->delete()) {
            return $this->success(200, 'logged out successful.', [], true);
        }

        return $this->error(500, 'logged out unsuccessful', [], false);
    }

    public function profile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if ($request->isMethod('post')) {
                if ($request->has('name') && $request->filled('name')) {
                    $user->name = $request->name;
                }
                if ($request->has('phone_number') && $request->filled('phone_number')) {
                    $user->phone_number = $request->phone_number;
                }

                if ($request->hasFile('profile_photo')) {
                    try {
                        // Upload file using helper function
                        $filePath = uploadFile(
                            $request->file('profile_photo'),
                            'profile-images',
                            'profile',
                            $user->profile_photo_path
                        );

                        $user->profile_photo_path = $filePath;
                    } catch (\Exception $e) {
                        \Log::error('Profile photo upload failed: ' . $e->getMessage());
                        // Continue without updating photo
                    }
                }

                $user->save();

                if ($request->filled('fcm_token')) {
                    $this->storeFcmToken($request, $user);
                }
            }

            $msg = $request->isMethod('post') ? 'Profile update successfully' : 'Profile fetched successfully';

            return response()->json([
                'status' => true,
                'message' => $msg,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_active' => $user->is_active,
                    'profile_url' => $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : null,
                ]
            ]);
        } catch (\Throwable $th) {
            return $this->error(500, $th->getMessage(), [], false);
        }
    }

    protected function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->getRoleNames()->first(),
            'created_at' => $user->created_at?->toIso8601String(),
            'updated_at' => $user->updated_at?->toIso8601String(),
        ];
    }
}
