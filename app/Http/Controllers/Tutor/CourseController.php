<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Module; // adjust to your actual module model

class CourseController extends Controller
{
    /**
     * Display a listing of courses (modules) assigned to the tutor.
     */
    public function index()
    {
        // Get the currently logged-in tutor
        $tutor = Auth::user();

        // Fetch modules assigned to this tutor
        // Adjust according to your database structure.
        // Example: if modules have a 'tutor_id' column:
        $modules = Module::where('tutor_id', $tutor->id)->get();

        // Alternatively, if you have a many-to-many relation:
        // $modules = $tutor->modules()->get();

        return view('tutor.courses.index', compact('modules'));
    }

    // Other methods (create, store, show, etc.) can be added as needed.
}