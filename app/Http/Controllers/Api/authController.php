<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class authController extends Controller
{
    //
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->with('roles')->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credenciales inválidas'
            ], 401);
        }

        $token = $user->createToken('flutter')->plainTextToken;
        return response()->json([
            'token' => $token,
            'user' => $user
        ], 200);
    }
}
