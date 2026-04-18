<?php

namespace App\Http\Controllers\Admission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class InquiryController extends Controller
{
    /**
     * Display inquiries list
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $type = $request->get('type');
        
        $query = DB::table('inquiries as i')
            ->select(
                'i.*',
                'u.first_name as created_by_first_name',
                'u.last_name as created_by_last_name',
                'assigned.first_name as assigned_first_name',
                'assigned.last_name as assigned_last_name'
            )
            ->leftJoin('users as u', 'i.created_by', '=', 'u.id')
            ->leftJoin('users as assigned', 'i.assigned_to', '=', 'assigned.id')
            ->orderByDesc('i.created_at');
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('i.inquiry_number', 'like', "%{$search}%")
                  ->orWhere('i.full_name', 'like', "%{$search}%")
                  ->orWhere('i.email', 'like', "%{$search}%")
                  ->orWhere('i.phone', 'like', "%{$search}%")
                  ->orWhere('i.subject', 'like', "%{$search}%");
            });
        }
        
        if ($status) {
            $query->where('i.status', $status);
        }
        
        if ($type) {
            $query->where('i.inquiry_type', $type);
        }
        
        $inquiries = $query->paginate(20)->withQueryString();
        
        $statistics = [
            'total' => DB::table('inquiries')->count(),
            'new' => DB::table('inquiries')->where('status', 'new')->count(),
            'in_progress' => DB::table('inquiries')->where('status', 'in_progress')->count(),
            'resolved' => DB::table('inquiries')->where('status', 'resolved')->count(),
            'closed' => DB::table('inquiries')->where('status', 'closed')->count(),
        ];
        
        $staff = DB::table('users')
            ->where('user_type', 'staff')
            ->orWhere('user_type', 'admin')
            ->select('id', 'first_name', 'last_name')
            ->get();
        
        return view('admission.inquiries.index', compact('inquiries', 'statistics', 'staff', 'search', 'status', 'type'));
    }
    
    /**
     * Show create inquiry form
     */
    public function create()
    {
        $staff = DB::table('users')
            ->where('user_type', 'staff')
            ->orWhere('user_type', 'admin')
            ->select('id', 'first_name', 'last_name')
            ->get();
        
        return view('admission.inquiries.create', compact('staff'));
    }
    
    /**
     * Store new inquiry
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'inquiry_type' => 'required|in:general,admission,program,payment,technical,complaint,other',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        try {
            $inquiryNumber = $this->generateInquiryNumber();
            
            $inquiryId = DB::table('inquiries')->insertGetId([
                'inquiry_number' => $inquiryNumber,
                'full_name' => $request->full_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'inquiry_type' => $request->inquiry_type,
                'subject' => $request->subject,
                'message' => $request->message,
                'priority' => $request->priority,
                'status' => 'new',
                'assigned_to' => $request->assigned_to,
                'notes' => $request->notes,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            Log::info('New inquiry created', [
                'inquiry_id' => $inquiryId,
                'inquiry_number' => $inquiryNumber,
                'created_by' => auth()->id()
            ]);
            
            return redirect()->route('admission.inquiries.show', $inquiryId)
                ->with('success', "Inquiry #{$inquiryNumber} created successfully.");
                
        } catch (\Exception $e) {
            Log::error('Failed to create inquiry: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create inquiry: ' . $e->getMessage());
        }
    }
    
    /**
     * Show inquiry details
     */
    public function show($id)
    {
        $inquiry = DB::table('inquiries as i')
            ->select(
                'i.*',
                'u.first_name as created_by_first_name',
                'u.last_name as created_by_last_name',
                'assigned.first_name as assigned_first_name',
                'assigned.last_name as assigned_last_name',
                'resolved.first_name as resolved_first_name',
                'resolved.last_name as resolved_last_name'
            )
            ->leftJoin('users as u', 'i.created_by', '=', 'u.id')
            ->leftJoin('users as assigned', 'i.assigned_to', '=', 'assigned.id')
            ->leftJoin('users as resolved', 'i.resolved_by', '=', 'resolved.id')
            ->where('i.id', $id)
            ->first();
        
        if (!$inquiry) {
            abort(404, 'Inquiry not found.');
        }
        
        // Get follow-up logs
        $followUps = DB::table('inquiry_follow_ups as f')
            ->select('f.*', 'u.first_name', 'u.last_name')
            ->leftJoin('users as u', 'f.created_by', '=', 'u.id')
            ->where('f.inquiry_id', $id)
            ->orderBy('f.created_at', 'desc')
            ->get();
        
        $staff = DB::table('users')
            ->where('user_type', 'staff')
            ->orWhere('user_type', 'admin')
            ->select('id', 'first_name', 'last_name')
            ->get();
        
        return view('admission.inquiries.show', compact('inquiry', 'followUps', 'staff'));
    }
    
    /**
     * Update inquiry
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:new,in_progress,resolved,closed',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'notes' => 'nullable|string',
            'resolution' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $updateData = [
                'status' => $request->status,
                'assigned_to' => $request->assigned_to,
                'priority' => $request->priority,
                'notes' => $request->notes,
                'updated_at' => now()
            ];
            
            if ($request->status === 'resolved' && $request->resolution) {
                $updateData['resolution'] = $request->resolution;
                $updateData['resolved_at'] = now();
                $updateData['resolved_by'] = auth()->id();
            }
            
            DB::table('inquiries')
                ->where('id', $id)
                ->update($updateData);
            
            return response()->json([
                'success' => true,
                'message' => 'Inquiry updated successfully.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update inquiry: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Add follow-up log
     */
    public function addFollowUp(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'follow_up_type' => 'required|in:call,email,meeting,note,other',
            'notes' => 'required|string',
            'next_follow_up_date' => 'nullable|date',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $followUpId = DB::table('inquiry_follow_ups')->insertGetId([
                'inquiry_id' => $id,
                'follow_up_type' => $request->follow_up_type,
                'notes' => $request->notes,
                'next_follow_up_date' => $request->next_follow_up_date,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Update inquiry status to in_progress if it's new
            DB::table('inquiries')
                ->where('id', $id)
                ->where('status', 'new')
                ->update([
                    'status' => 'in_progress',
                    'updated_at' => now()
                ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Follow-up log added successfully.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add follow-up: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Display follow-up logs
     */
    public function followUpLogs(Request $request)
    {
        $search = $request->get('search');
        $type = $request->get('type');
        
        $query = DB::table('inquiry_follow_ups as f')
            ->select(
                'f.*',
                'i.inquiry_number',
                'i.full_name',
                'i.subject',
                'i.status as inquiry_status',
                'u.first_name',
                'u.last_name'
            )
            ->leftJoin('inquiries as i', 'f.inquiry_id', '=', 'i.id')
            ->leftJoin('users as u', 'f.created_by', '=', 'u.id')
            ->orderByDesc('f.created_at');
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('i.inquiry_number', 'like', "%{$search}%")
                  ->orWhere('i.full_name', 'like', "%{$search}%")
                  ->orWhere('i.subject', 'like', "%{$search}%")
                  ->orWhere('f.notes', 'like', "%{$search}%");
            });
        }
        
        if ($type) {
            $query->where('f.follow_up_type', $type);
        }
        
        $followUps = $query->paginate(20)->withQueryString();
        
        $statistics = [
            'total' => DB::table('inquiry_follow_ups')->count(),
            'calls' => DB::table('inquiry_follow_ups')->where('follow_up_type', 'call')->count(),
            'emails' => DB::table('inquiry_follow_ups')->where('follow_up_type', 'email')->count(),
            'meetings' => DB::table('inquiry_follow_ups')->where('follow_up_type', 'meeting')->count(),
        ];
        
        return view('admission.inquiries.follow-ups', compact('followUps', 'statistics', 'search', 'type'));
    }
    
    /**
     * Generate inquiry number
     */
    private function generateInquiryNumber()
    {
        $year = date('Y');
        $month = date('m');
        $count = DB::table('inquiries')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count() + 1;
        $sequence = str_pad($count, 4, '0', STR_PAD_LEFT);
        return "INQ/{$year}/{$month}/{$sequence}";
    }
    
    /**
     * Export inquiries to CSV
     */
    public function export(Request $request)
    {
        $query = DB::table('inquiries')
            ->orderByDesc('created_at');
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        $inquiries = $query->get();
        
        $filename = 'inquiries_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($inquiries) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, [
                'Inquiry No', 'Date', 'Full Name', 'Email', 'Phone', 'Type', 
                'Subject', 'Priority', 'Status', 'Assigned To', 'Resolution'
            ]);
            
            foreach ($inquiries as $inquiry) {
                $assignedTo = DB::table('users')
                    ->where('id', $inquiry->assigned_to)
                    ->value(DB::raw("CONCAT(first_name, ' ', last_name)"));
                
                fputcsv($file, [
                    $inquiry->inquiry_number,
                    Carbon::parse($inquiry->created_at)->format('d/m/Y H:i'),
                    $inquiry->full_name,
                    $inquiry->email,
                    $inquiry->phone,
                    ucfirst($inquiry->inquiry_type),
                    $inquiry->subject,
                    ucfirst($inquiry->priority),
                    ucfirst(str_replace('_', ' ', $inquiry->status)),
                    $assignedTo ?? 'Unassigned',
                    $inquiry->resolution ?? ''
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}