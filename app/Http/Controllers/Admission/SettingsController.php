<?php

namespace App\Http\Controllers\Admission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class SettingsController extends Controller
{
    /**
     * Display admission calendar settings
     */
    public function admissionCalendar(Request $request)
    {
        // Get all calendar events
        $events = DB::table('admission_calendar_events')
            ->orderBy('event_date', 'asc')
            ->get();
        
        // Get upcoming events
        $upcomingEvents = DB::table('admission_calendar_events')
            ->where('event_date', '>=', Carbon::today())
            ->orderBy('event_date', 'asc')
            ->limit(10)
            ->get();
        
        // Get past events
        $pastEvents = DB::table('admission_calendar_events')
            ->where('event_date', '<', Carbon::today())
            ->orderBy('event_date', 'desc')
            ->limit(10)
            ->get();
        
        // Get active intake settings
        $intakeSettings = DB::table('admission_settings')
            ->where('category', 'intake')
            ->get()
            ->keyBy('key');
        
        // Get academic years
        $academicYears = DB::table('academic_years')
            ->orderBy('start_date', 'desc')
            ->get();
        
        // Get event types for filter
        $eventTypes = DB::table('admission_calendar_events')
            ->select('event_type')
            ->distinct()
            ->pluck('event_type');
        
        return view('admission.settings.calendar', compact(
            'events', 'upcomingEvents', 'pastEvents', 'intakeSettings', 'academicYears', 'eventTypes'
        ));
    }
    
    /**
     * Store calendar event
     */
    public function storeCalendarEvent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'required|date',
            'event_type' => 'required|in:application_deadline,admission_letter,registration,orientation,exam,holiday,other',
            'color' => 'nullable|string|max:20',
            'is_public' => 'boolean',
            'reminder_days' => 'nullable|integer|min:0|max:30',
        ]);
        
        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        try {
            $eventId = DB::table('admission_calendar_events')->insertGetId([
                'title' => $request->title,
                'description' => $request->description,
                'event_date' => $request->event_date,
                'event_type' => $request->event_type,
                'color' => $request->color ?? '#007bff',
                'is_public' => $request->is_public ?? 1,
                'reminder_days' => $request->reminder_days,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Clear calendar cache
            Cache::forget('admission_calendar_events');
            
            Log::info('Calendar event created', ['event_id' => $eventId, 'created_by' => auth()->id()]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Event created successfully',
                    'event_id' => $eventId
                ]);
            }
            
            return redirect()->route('admission.settings.calendar')
                ->with('success', 'Event created successfully');
                
        } catch (\Exception $e) {
            Log::error('Failed to create calendar event: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to create event'], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to create event: ' . $e->getMessage());
        }
    }
    
    /**
     * Update calendar event
     */
    public function updateCalendarEvent(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'required|date',
            'event_type' => 'required|in:application_deadline,admission_letter,registration,orientation,exam,holiday,other',
            'color' => 'nullable|string|max:20',
            'is_public' => 'boolean',
            'reminder_days' => 'nullable|integer|min:0|max:30',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        
        try {
            DB::table('admission_calendar_events')
                ->where('id', $id)
                ->update([
                    'title' => $request->title,
                    'description' => $request->description,
                    'event_date' => $request->event_date,
                    'event_type' => $request->event_type,
                    'color' => $request->color ?? '#007bff',
                    'is_public' => $request->is_public ?? 1,
                    'reminder_days' => $request->reminder_days,
                    'updated_at' => now(),
                ]);
            
            Cache::forget('admission_calendar_events');
            
            return response()->json([
                'success' => true,
                'message' => 'Event updated successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update event: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Delete calendar event
     */
    public function deleteCalendarEvent($id)
    {
        try {
            DB::table('admission_calendar_events')->where('id', $id)->delete();
            Cache::forget('admission_calendar_events');
            
            return response()->json([
                'success' => true,
                'message' => 'Event deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete event: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update intake settings
     */
    public function updateIntakeSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_intake' => 'required|in:March,September',
            'application_deadline_march' => 'required|date',
            'application_deadline_september' => 'required|date',
            'announcement_date_march' => 'required|date',
            'announcement_date_september' => 'required|date',
            'registration_deadline_march' => 'required|date',
            'registration_deadline_september' => 'required|date',
            'orientation_date_march' => 'required|date',
            'orientation_date_september' => 'required|date',
            'classes_start_march' => 'required|date',
            'classes_start_september' => 'required|date',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        try {
            $settings = [
                'current_intake' => $request->current_intake,
                'application_deadline_march' => $request->application_deadline_march,
                'application_deadline_september' => $request->application_deadline_september,
                'announcement_date_march' => $request->announcement_date_march,
                'announcement_date_september' => $request->announcement_date_september,
                'registration_deadline_march' => $request->registration_deadline_march,
                'registration_deadline_september' => $request->registration_deadline_september,
                'orientation_date_march' => $request->orientation_date_march,
                'orientation_date_september' => $request->orientation_date_september,
                'classes_start_march' => $request->classes_start_march,
                'classes_start_september' => $request->classes_start_september,
            ];
            
            foreach ($settings as $key => $value) {
                DB::table('admission_settings')->updateOrInsert(
                    ['key' => $key, 'category' => 'intake'],
                    ['value' => $value, 'updated_at' => now(), 'updated_by' => auth()->id()]
                );
            }
            
            Cache::forget('admission_intake_settings');
            
            return redirect()->route('admission.settings.calendar')
                ->with('success', 'Intake settings updated successfully');
                
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }
    
    /**
     * Display workflow settings
     */
    public function workflow(Request $request)
    {
        // Get workflow stages
        $workflowStages = DB::table('admission_workflow_stages')
            ->orderBy('stage_order', 'asc')
            ->get();
        
        // Get workflow rules
        $workflowRules = DB::table('admission_workflow_rules')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get notification templates
        $notificationTemplates = DB::table('admission_notification_templates')
            ->orderBy('type', 'asc')
            ->get();
        
        // Get auto-selection criteria
        $selectionCriteria = DB::table('admission_selection_criteria')->first();
        
        if (!$selectionCriteria) {
            $selectionCriteria = (object)[
                'csee_weight' => 60,
                'acsee_weight' => 30,
                'division_bonus' => 10,
                'auto_select_enabled' => 1,
                'min_ranking_score' => 50
            ];
        }
        
        // Get staff roles for assignment
        $staffRoles = DB::table('roles')
            ->whereIn('name', ['admission_officer', 'reviewer', 'finance', 'registrar', 'admin'])
            ->get();
        
        return view('admission.settings.workflow', compact(
            'workflowStages', 'workflowRules', 'notificationTemplates', 
            'selectionCriteria', 'staffRoles'
        ));
    }
    
    /**
     * Store workflow stage
     */
    public function storeWorkflowStage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'stage_name' => 'required|string|max:100',
            'stage_code' => 'required|string|max:50|unique:admission_workflow_stages,stage_code',
            'stage_order' => 'required|integer|min:1',
            'responsible_role' => 'required|string|max:100',
            'description' => 'nullable|string',
            'is_required' => 'boolean',
            'days_to_complete' => 'nullable|integer|min:1',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        try {
            DB::table('admission_workflow_stages')->insert([
                'stage_name' => $request->stage_name,
                'stage_code' => $request->stage_code,
                'stage_order' => $request->stage_order,
                'responsible_role' => $request->responsible_role,
                'description' => $request->description,
                'is_required' => $request->is_required ?? 1,
                'days_to_complete' => $request->days_to_complete,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            Cache::forget('admission_workflow_stages');
            
            return redirect()->back()->with('success', 'Workflow stage added successfully');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to add stage: ' . $e->getMessage());
        }
    }
    
    /**
     * Update workflow stage
     */
    public function updateWorkflowStage(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'stage_name' => 'required|string|max:100',
            'stage_order' => 'required|integer|min:1',
            'responsible_role' => 'required|string|max:100',
            'description' => 'nullable|string',
            'is_required' => 'boolean',
            'days_to_complete' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        
        try {
            DB::table('admission_workflow_stages')
                ->where('id', $id)
                ->update([
                    'stage_name' => $request->stage_name,
                    'stage_order' => $request->stage_order,
                    'responsible_role' => $request->responsible_role,
                    'description' => $request->description,
                    'is_required' => $request->is_required ?? 1,
                    'days_to_complete' => $request->days_to_complete,
                    'is_active' => $request->is_active ?? 1,
                    'updated_at' => now(),
                ]);
            
            Cache::forget('admission_workflow_stages');
            
            return response()->json(['success' => true, 'message' => 'Stage updated successfully']);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update stage'], 500);
        }
    }
    
    /**
     * Delete workflow stage
     */
    public function deleteWorkflowStage($id)
    {
        try {
            DB::table('admission_workflow_stages')->where('id', $id)->delete();
            Cache::forget('admission_workflow_stages');
            
            return response()->json(['success' => true, 'message' => 'Stage deleted successfully']);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete stage'], 500);
        }
    }
    
    /**
     * Update selection criteria
     */
    public function updateSelectionCriteria(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'csee_weight' => 'required|integer|min:0|max:100',
            'acsee_weight' => 'required|integer|min:0|max:100',
            'division_bonus' => 'required|integer|min:0|max:50',
            'auto_select_enabled' => 'boolean',
            'min_ranking_score' => 'required|integer|min:0|max:100',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        try {
            DB::table('admission_selection_criteria')->updateOrInsert(
                ['id' => 1],
                [
                    'csee_weight' => $request->csee_weight,
                    'acsee_weight' => $request->acsee_weight,
                    'division_bonus' => $request->division_bonus,
                    'auto_select_enabled' => $request->auto_select_enabled ?? 0,
                    'min_ranking_score' => $request->min_ranking_score,
                    'updated_by' => auth()->id(),
                    'updated_at' => now(),
                ]
            );
            
            Cache::forget('admission_selection_criteria');
            
            return redirect()->back()->with('success', 'Selection criteria updated successfully');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update criteria: ' . $e->getMessage());
        }
    }
    
    /**
     * Store notification template
     */
    public function storeNotificationTemplate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|max:100|unique:admission_notification_templates,type',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'is_active' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        try {
            DB::table('admission_notification_templates')->insert([
                'type' => $request->type,
                'subject' => $request->subject,
                'body' => $request->body,
                'is_active' => $request->is_active ?? 1,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            Cache::forget('admission_notification_templates');
            
            return redirect()->back()->with('success', 'Notification template added successfully');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to add template: ' . $e->getMessage());
        }
    }
    
    /**
     * Update notification template
     */
    public function updateNotificationTemplate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'is_active' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        
        try {
            DB::table('admission_notification_templates')
                ->where('id', $id)
                ->update([
                    'subject' => $request->subject,
                    'body' => $request->body,
                    'is_active' => $request->is_active ?? 1,
                    'updated_at' => now(),
                ]);
            
            Cache::forget('admission_notification_templates');
            
            return response()->json(['success' => true, 'message' => 'Template updated successfully']);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update template'], 500);
        }
    }
    
    /**
     * Delete notification template
     */
    public function deleteNotificationTemplate($id)
    {
        try {
            DB::table('admission_notification_templates')->where('id', $id)->delete();
            Cache::forget('admission_notification_templates');
            
            return response()->json(['success' => true, 'message' => 'Template deleted successfully']);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete template'], 500);
        }
    }
    
    /**
     * Get calendar events for JSON (AJAX)
     */
    public function getCalendarEvents(Request $request)
    {
        $start = $request->get('start');
        $end = $request->get('end');
        
        $query = DB::table('admission_calendar_events');
        
        if ($start && $end) {
            $query->whereBetween('event_date', [$start, $end]);
        }
        
        $events = $query->get();
        
        $formattedEvents = [];
        foreach ($events as $event) {
            $formattedEvents[] = [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->event_date,
                'color' => $event->color,
                'description' => $event->description,
                'event_type' => $event->event_type,
            ];
        }
        
        return response()->json($formattedEvents);
    }
}