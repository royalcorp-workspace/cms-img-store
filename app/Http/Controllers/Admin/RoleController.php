<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class RoleController extends Controller
{
    public function index()
    {
        return view('pages.roles.index');
    }
}
