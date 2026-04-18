<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LecturerController extends Controller
{
    public function index()
    {
        return view('dashboards.student'); // View yako ya lecturer dashboard
    }
}
