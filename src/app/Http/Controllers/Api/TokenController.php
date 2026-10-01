<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class TokenController extends Controller
{
    /** トークン発行 */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'email'       => ['required', 'email'],
            'password'    => ['required'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['メールアドレスまたはパスワードが正しくありません。'],
            ]);
        }

        $token = $user->createToken($request->input('device_name', 'api'))->plainTextToken;


        return response()->json([
            'token'      => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /** トークン失効 */
    public function destroy(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        // Cookie認証の場合はトークンが存在しないので、トークン認証の時だけ削除
        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        return response()->json(['message' => 'ログアウトしました。']);
    }
}