<?php

namespace App\Http\Controllers;

class PermissionController extends Controller
{
    public function index()
    {
        return view('pages.permissions.index');
    }
}
