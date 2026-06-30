<?php

namespace App\Http\Controllers;

class InventoryController extends Controller
{
    public function index()
    {
        return view('pages.inventory.index');
    }

    public function create()
    {
        return view('pages.inventory.create');
    }
}
