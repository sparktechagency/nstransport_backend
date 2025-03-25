<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            "passcode" => "required|string|min: 6",
        ]);
        $exist_user = User::where('passcode', $request->passcode)->first();
        if (! $exist_user) {
            return response()->json([
                'error' => 'The provided credential are not correct',
            ], 422);
        }
        $attempt_login = Auth::login($exist_user);
        $user          = Auth::user();
        $token         = $user->createToken('NS Transport')->plainTextToken;
        return response()->json([
            'token' => $token,
            'user'  => $user,
        ]);
    }

    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->tokens()->delete();

            return response()->json([
                'message' => 'Logged out successfully',
            ]);
        }

        return response()->json([
            'error' => 'User not authenticated',
        ], 401);
    }
}
