<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(Request $request)
    {
        info($request->all());

        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        $token = Auth::login($user);

        return response()->json([
            'user' => $user,
            'token' => $token
        ], 201);
    }

    public function login(Request $request)
    {
        try {
            $phoneNumber = $request->validate([
                'phone_number' => 'required|exists:users,phone_number'
            ]);

            $user = User::where('phone_number', $phoneNumber)->first();
            $token = JWTAuth::fromUser($user);

            return $this->successResponse($token, 'Login successful');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to login' . $e->getMessage(), 500);
        }
    }

    public function me($phoneNumber)
    {
        try {
            $user = User::where('phone_number', $phoneNumber)->first();

            if (!$user) {
                return $this->errorResponse('User not found', 404);
            }

            return $this->successResponse($user, 'User retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve user: ' . $e->getMessage(), 500);
        }
    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Logged out']);
    }

    public function refresh()
    {
        return response()->json([
            'token' => auth()->refresh()
        ]);
    }
}

