<?php

namespace App\Http\Controllers\Admission;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DocumentController extends Controller
{
    /**
     * Display list of documents
     */
    public function index(Request $request)
    {
        $query = Document::with(['application.personalInfo', 'uploader'])
            ->orderBy('created_at', 'desc');
        
        // Apply filters
        if ($request->verification_status) {
            $query->where('verification_status', $request->verification_status);
        }
        
        if ($request->document_type) {
            $query->where('document_type', $request->document_type);
        }
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('document_number', 'like', "%{$request->search}%")
                  ->orWhere('title', 'like', "%{$request->search}%")
                  ->orWhereHas('application.personalInfo', function($sq) use ($request) {
                      $sq->where('first_name', 'like', "%{$request->search}%")
                         ->orWhere('last_name', 'like', "%{$request->search}%");
                  });
            });
        }
        
        $documents = $query->paginate(20)->withQueryString();
        
        // FIXED: Use where() instead of scopes
        $statistics = [
            'total' => Document::count(),
            'pending' => Document::where('verification_status', 'pending')->count(),
            'verified' => Document::where('verification_status', 'verified')->count(),
            'rejected' => Document::where('verification_status', 'rejected')->count(),
        ];
        
        $documentTypes = DB::table('document_types')->where('is_active', 1)->orderBy('sort_order')->get();
        
        return view('admission.documents.index', compact('documents', 'statistics', 'documentTypes'));
    }
    
    /**
     * Show verification queue
     */
    public function verificationQueue(Request $request)
    {
        // FIXED: Use where() instead of pending() scope
        $documents = Document::with(['application.personalInfo'])
            ->where('verification_status', 'pending')
            ->orderBy('created_at', 'asc')
            ->paginate(20);
        
        $statistics = [
            'total_pending' => Document::where('verification_status', 'pending')->count(),
        ];
        
        return view('admission.documents.verification-queue', compact('documents', 'statistics'));
    }
    
    /**
     * Upload document (AJAX)
     */
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'document_type' => 'required|string|max:50',
            'application_id' => 'required|exists:applications,id',
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'errors' => $validator->errors()
            ], 422);
        }
        
        DB::beginTransaction();
        
        try {
            $file = $request->file('document');
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $fileName = time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension;
            
            // Store file
            $path = $file->storeAs('documents/' . date('Y/m'), $fileName, 'public');
            
            // Generate document number
            $documentNumber = $this->generateDocumentNumber();
            
            // Create document record
            $document = Document::create([
                'document_number' => $documentNumber,
                'title' => $request->title ?? $originalName,
                'description' => $request->description,
                'document_type' => $request->document_type,
                'application_id' => $request->application_id,
                'user_id' => auth()->id(),
                'original_name' => $originalName,
                'file_name' => $fileName,
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'status' => 'pending',
                'verification_status' => 'pending',
                'uploaded_by' => auth()->id(),
                'uploaded_at' => now(),
            ]);
            
            DB::commit();
            
            Log::info('Document uploaded successfully', [
                'document_id' => $document->id,
                'document_number' => $documentNumber,
                'application_id' => $request->application_id,
                'uploaded_by' => auth()->id()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully!',
                'document' => $document
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Document upload failed: ' . $e->getMessage());
            
            // Delete file if uploaded
            if (isset($path) && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Verify document (AJAX)
     */
    public function verify(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'verification_status' => 'required|in:verified,rejected',
            'verification_notes' => 'nullable|string',
            'rejection_reason' => 'required_if:verification_status,rejected|nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $document = Document::findOrFail($id);
            
            $updateData = [
                'verification_status' => $request->verification_status,
                'verification_notes' => $request->verification_notes,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'status' => $request->verification_status === 'verified' ? 'approved' : 'rejected',
            ];
            
            if ($request->verification_status === 'rejected') {
                $updateData['rejection_reason'] = $request->rejection_reason;
            }
            
            $document->update($updateData);
            
            Log::info('Document verified', [
                'document_id' => $id,
                'status' => $request->verification_status,
                'verified_by' => auth()->id()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Document ' . $request->verification_status . ' successfully!'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Document verification failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Verification failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Download document
     */
    public function download($id)
    {
        try {
            $document = Document::findOrFail($id);
            
            if (!Storage::disk('public')->exists($document->file_path)) {
                abort(404, 'File not found');
            }
            
            return Storage::disk('public')->download($document->file_path, $document->original_name);
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to download document: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete document
     */
    public function destroy($id)
    {
        try {
            $document = Document::findOrFail($id);
            
            // Delete file from storage
            if (Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
            
            // Delete record
            $document->delete();
            
            Log::info('Document deleted', [
                'document_id' => $id,
                'deleted_by' => auth()->id()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete document: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Search applications (AJAX)
     */
    public function searchApplications(Request $request)
    {
        $search = $request->get('search');
        
        if (strlen($search) < 2) {
            return response()->json(['success' => false, 'message' => 'Search term too short'], 400);
        }
        
        $applications = Application::with('personalInfo')
            ->where(function($q) use ($search) {
                $q->where('application_number', 'like', "%{$search}%")
                  ->orWhereHas('personalInfo', function($sq) use ($search) {
                      $sq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%");
                  });
            })
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function($app) {
                return [
                    'id' => $app->id,
                    'application_number' => $app->application_number,
                    'first_name' => $app->personalInfo->first_name ?? '',
                    'last_name' => $app->personalInfo->last_name ?? '',
                    'status' => $app->status,
                ];
            });
        
        return response()->json([
            'success' => true,
            'data' => $applications
        ]);
    }
    
    /**
     * Generate document number
     */
    private function generateDocumentNumber()
    {
        $year = date('Y');
        $month = date('m');
        $count = Document::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count() + 1;
        $sequence = str_pad($count, 4, '0', STR_PAD_LEFT);
        return "DOC/{$year}/{$month}/{$sequence}";
    }
}