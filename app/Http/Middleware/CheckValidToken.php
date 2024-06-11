<?php

namespace App\Http\Middleware;

use App\Models\UserToken;
use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

class CheckValidToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $token = JWTAuth::getToken();
            $user = JWTAuth::toUser($token);
            $isValid = UserToken::where('user_id', $user?->id)->where('token', $token)->exists();

            if (!$isValid) {
                JWTAuth::invalidate($token);
                return response()->json(['success' => false, 'message' => 'Tài khoản đã đăng nhập ở 1 nơi khác. Vui lòng thử lại.'], 401);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['success' => false, 'message' => 'Token hết hạn. Vui lòng đăng nhập lại.'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['success' => false, 'message' => 'Tài khoản đã đăng nhập ở 1 nơi khác. Vui lòng thử lại.'], 401);
        }

        return $next($request);
    }
}
