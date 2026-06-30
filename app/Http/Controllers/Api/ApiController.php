<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{
    protected function successResponse($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'status_code' => $code,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function errorResponse(string $message = 'Error', int $code = 400, $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'status_code' => $code,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }

    protected function getAuthenticatedUser(): ?User
    {
        $userId = request()->get('auth_user_id');
        if (!$userId) {
            return null;
        }
        
        return User::find($userId);
    }
}