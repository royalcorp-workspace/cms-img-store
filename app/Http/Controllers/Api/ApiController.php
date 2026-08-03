<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;

class ApiController extends Controller
{
    protected function getAuthenticatedUser(): ?User
    {
        $userId = request()->get('auth_user_id');

        if (!$userId) {
            return null;
        }

        return User::find($userId);
    }
}