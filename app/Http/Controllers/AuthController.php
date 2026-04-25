<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerProfileUpdateRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Customer;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => $request->password,
                'phone'    => $request->phone,
                'role'     => 'admin',
            ]);

            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'message' => 'Registration successful.',
                'data'    => [
                    'user'  => $user,
                    'token' => $token,
                ],
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Registration failed.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (! $user || ! Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'Invalid credentials.',
                ], 401);
            }

            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'message' => 'Login successful.',
                'data'    => [
                    'user'  => $user,
                    'token' => $token,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Login failed.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Logged out successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Logout failed.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function user(Request $request): JsonResponse
    {
        try {
            return response()->json([
                'data' => $request->user(),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch user.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // -------------------------------------------------------------------------
    // Storefront Customer Auth
    // -------------------------------------------------------------------------

    public function customerRegister(RegisterRequest $request): JsonResponse
    {
        try {
            $data = DB::transaction(function () use ($request) {
                $user = User::create([
                    'name'     => $request->name,
                    'email'    => $request->email,
                    'password' => $request->password,
                    'phone'    => $request->phone,
                    'role'     => 'customer',
                ]);

                $customer = Customer::create([
                    'user_id' => $user->id,
                    'name'    => $user->name,
                    'email'   => $user->email,
                    'phone'   => $user->phone,
                ]);

                $token = $user->createToken('customer-token')->plainTextToken;

                return compact('user', 'customer', 'token');
            });

            return response()->json([
                'message' => 'Registration successful.',
                'data'    => $data,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Registration failed.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function customerLogin(LoginRequest $request): JsonResponse
    {
        try {
            $user = User::where('email', $request->email)
                ->where('role', 'customer')
                ->first();

            if (! $user || ! Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'Invalid credentials.',
                ], 401);
            }

            if (! $user->is_active) {
                return response()->json([
                    'message' => 'Your account has been deactivated.',
                ], 403);
            }

            $token = $user->createToken('customer-token')->plainTextToken;

            return response()->json([
                'message' => 'Login successful.',
                'data'    => [
                    'user'     => $user,
                    'customer' => $user->customer,
                    'token'    => $token,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Login failed.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function customerLogout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Logged out successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Logout failed.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function customerProfile(Request $request): JsonResponse
    {
        try {
            $user = $request->user()->load('customer');

            return response()->json([
                'message' => 'Profile retrieved successfully.',
                'data'    => $user,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve profile.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function customerProfileUpdate(CustomerProfileUpdateRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $data = $request->validated();

            $user->update([
                'name'  => $data['name'] ?? $user->name,
                'phone' => $data['phone'] ?? $user->phone,
            ]);

            $user->customer->update([
                'name'             => $data['name'] ?? $user->customer->name,
                'phone'            => $data['phone'] ?? $user->customer->phone,
                'shipping_address' => $data['shipping_address'] ?? $user->customer->shipping_address,
            ]);

            return response()->json([
                'message' => 'Profile updated successfully.',
                'data'    => $user->load('customer'),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to update profile.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
