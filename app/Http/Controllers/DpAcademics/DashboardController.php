<?php

namespace App\Http\Controllers\DpAcademics;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboards.academic');
    }
}