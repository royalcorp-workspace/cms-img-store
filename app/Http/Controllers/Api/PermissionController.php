<?php

namespace App\Http\Controllers\Api;

use App\Models\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PermissionController extends ApiController
{
    public function index(): JsonResponse
    {
        $permissions = Permission::all();
        return $this->successResponse($permissions);
    }

    public function store(Request $request): JsonResponse
    {
        $permission = Permission::create($request->all());
        return $this->successResponse($permission, 'Permission created', 201);
    }

    public function show(string $id): JsonResponse
    {
        $permission = Permission::find($id);
        if (!$permission) {
            return $this->errorResponse('Permission not found', 404);
        }
        return $this->successResponse($permission);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $permission = Permission::find($id);
        if (!$permission) {
            return $this->errorResponse('Permission not found', 404);
        }
        $permission->update($request->all());
        return $this->successResponse($permission, 'Permission updated');
    }

    public function destroy(string $id): JsonResponse
    {
        $permission = Permission::find($id);
        if (!$permission) {
            return $this->errorResponse('Permission not found', 404);
        }
        $permission->delete();
        return $this->successResponse(null, 'Permission deleted', 204);
    }
}