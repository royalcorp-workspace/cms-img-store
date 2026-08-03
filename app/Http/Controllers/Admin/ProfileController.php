<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    public function show()
    {
        return view('pages.profile.show');
    }
}
