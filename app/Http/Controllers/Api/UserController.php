<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends ApiController
{
    public function index(): JsonResponse
    {
        $users = User::all();
        return $this->successResponse($users);
    }

    public function store(Request $request): JsonResponse
    {
        $user = User::create($request->all());
        return $this->successResponse($user, 'User created', 201);
    }

    public function show(string $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return $this->errorResponse('User not found', 404);
        }
        return $this->successResponse($user);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return $this->errorResponse('User not found', 404);
        }
        $user->update($request->all());
        return $this->successResponse($user, 'User updated');
    }

    public function destroy(string $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return $this->errorResponse('User not found', 404);
        }
        $user->delete();
        return $this->successResponse(null, 'User deleted', 204);
    }
}