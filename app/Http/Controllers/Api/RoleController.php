<?php

namespace App\Http\Controllers\Api;

use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends ApiController
{
    public function index(): JsonResponse
    {
        $roles = Role::all();
        return $this->successResponse($roles);
    }

    public function store(Request $request): JsonResponse
    {
        $role = Role::create($request->all());
        return $this->successResponse($role, 'Role created', 201);
    }

    public function show(string $id): JsonResponse
    {
        $role = Role::find($id);
        if (!$role) {
            return $this->errorResponse('Role not found', 404);
        }
        return $this->successResponse($role);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $role = Role::find($id);
        if (!$role) {
            return $this->errorResponse('Role not found', 404);
        }
        $role->update($request->all());
        return $this->successResponse($role, 'Role updated');
    }

    public function destroy(string $id): JsonResponse
    {
        $role = Role::find($id);
        if (!$role) {
            return $this->errorResponse('Role not found', 404);
        }
        $role->delete();
        return $this->successResponse(null, 'Role deleted', 204);
    }
}