<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DeanController extends Controller
{
    public function index()
    {
        return view('dashboards.dean'); // TODO: Add admin logic later
    }
}
