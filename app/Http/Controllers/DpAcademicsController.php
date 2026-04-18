<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DpAcademicsController extends Controller
{
    public function index()
    {
        // Return the dashboard view for Deputy Principal Academics
        return view('dashboards.academic');
    }
}