<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Student;
use App\Models\Application;
use App\Models\Programme;
use App\Models\AcademicYear;

class ProfileController extends Controller
{
    /**
     * Get last login time
     */
    private function getLastLogin()
    {
        $user = Auth::user();
        $lastLogin = $user->last_login_at;
        
        if ($lastLogin) {
            return date('d M, Y H:i:s', strtotime($lastLogin));
        }
        
        return 'First login';
    }

    /**
     * Get current academic year
     */
    private function getCurrentAcademicYear()
    {
        $currentYear = AcademicYear::where('is_active', true)
            ->orWhere('status', 'active')
            ->first();
        
        if ($currentYear) {
            return $currentYear->name;
        }
        
        // Fallback: get from student's academic year
        $student = Student::where('user_id', Auth::id())->first();
        if ($student && $student->academicYear) {
            return $student->academicYear->name;
        }
        
        return date('Y') . '/' . (date('Y') + 1);
    }

    /**
     * Display student profile (READ ONLY - no editing)
     */
    public function index()
    {
        $user = Auth::user();
        $student = Student::with(['programme', 'academicYear'])
            ->where('user_id', $user->id)
            ->firstOrFail();
        
        // Get application data if exists
        $application = null;
        if ($student->application_id) {
            $application = Application::where('id', $student->application_id)
                ->orWhere('application_number', $student->application_id)
                ->first();
        }
        
        $currentAcademicYear = $this->getCurrentAcademicYear();
        $lastLogin = $this->getLastLogin();
        
        return view('student.profile', compact(
            'user', 
            'student', 
            'application',
            'currentAcademicYear',
            'lastLogin'
        ));
    }

    /**
     * Display registration form for editing (EDITABLE fields only)
     */
    public function edit()
    {
        $user = Auth::user();
        $student = Student::with(['programme', 'academicYear'])
            ->where('user_id', $user->id)
            ->firstOrFail();
        
        // Get application data if exists
        $application = null;
        if ($student->application_id) {
            $application = Application::where('id', $student->application_id)
                ->orWhere('application_number', $student->application_id)
                ->first();
        }
        
        $currentAcademicYear = $this->getCurrentAcademicYear();
        $lastLogin = $this->getLastLogin();
        
        // Get programmes for dropdown (if needed for reference only)
        $programmes = Programme::where('is_active', true)->get();
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        
        return view('student.registration-form', compact(
            'user',
            'student',
            'application',
            'currentAcademicYear',
            'lastLogin',
            'programmes',
            'academicYears'
        ));
    }

    /**
     * Update student profile (ONLY specific editable fields)
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->firstOrFail();
        
        // Validation - ONLY for editable fields
        $request->validate([
            // Personal Information (from users table)
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'nullable|in:male,female,other',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|email|unique:users,email,' . $user->id,
            
            // Contact Information
            'permanent_address' => 'nullable|string|max:500',
            'current_address' => 'nullable|string|max:500',
            
            // Guardian Information (from students table)
            'guardian_name' => 'nullable|string|max:255',
            'guardian_phone' => 'nullable|string|max:20',
            
            // Student Information (from students table)
            'study_mode' => 'nullable|string|max:255',
            
            // Application Information (from applications table if exists)
            'nationality' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'sponsorship_type' => 'nullable|string|max:255',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Update users table (editable fields)
            $userData = [
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'gender' => $request->gender,
                'phone' => $request->phone,
                'email' => $request->email,
            ];
            
            // Add address fields if they exist in users table
            if ($request->has('permanent_address')) {
                $userData['permanent_address'] = $request->permanent_address;
            }
            if ($request->has('current_address')) {
                $userData['current_address'] = $request->current_address;
            }
            
            $user->update($userData);
            
            // Update students table (editable fields)
            $studentData = [];
            
            if ($request->has('guardian_name')) {
                $studentData['guardian_name'] = $request->guardian_name;
            }
            if ($request->has('guardian_phone')) {
                $studentData['guardian_phone'] = $request->guardian_phone;
            }
            if ($request->has('study_mode')) {
                $studentData['study_mode'] = $request->study_mode;
            }
            
            if (!empty($studentData)) {
                $student->update($studentData);
            }
            
            // Update applications table if exists
            if ($student->application_id) {
                $application = Application::where('id', $student->application_id)
                    ->orWhere('application_number', $student->application_id)
                    ->first();
                
                if ($application) {
                    $appData = [];
                    if ($request->has('nationality')) {
                        $appData['nationality'] = $request->nationality;
                    }
                    if ($request->has('region')) {
                        $appData['region'] = $request->region;
                    }
                    if ($request->has('district')) {
                        $appData['district'] = $request->district;
                    }
                    if ($request->has('sponsorship_type')) {
                        $appData['sponsorship_type'] = $request->sponsorship_type;
                    }
                    
                    if (!empty($appData)) {
                        $application->update($appData);
                    }
                }
            }
            
            DB::commit();
            
            return redirect()->route('student.profile')
                ->with('success', 'Profile updated successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Profile update failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update profile: ' . $e->getMessage());
        }
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);
        
        $user = Auth::user();
        
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }
        
        $user->password = Hash::make($request->new_password);
        $user->must_change_password = false;
        $user->save();
        
        return redirect()->back()->with('success', 'Password changed successfully!');
    }

    /**
     * Upload profile photo
     */
    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
        
        try {
            $user = Auth::user();
            
            if ($request->hasFile('profile_photo')) {
                // Delete old photo if exists
                if ($user->profile_photo && file_exists(storage_path('app/public/' . $user->profile_photo))) {
                    unlink(storage_path('app/public/' . $user->profile_photo));
                }
                
                $path = $request->file('profile_photo')->store('profile_photos', 'public');
                $user->profile_photo = $path;
                $user->save();
            }
            
            return redirect()->back()->with('success', 'Profile photo updated successfully!');
            
        } catch (\Exception $e) {
            Log::error('Profile photo upload failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return redirect()->back()->with('error', 'Failed to upload photo: ' . $e->getMessage());
        }
    }
}