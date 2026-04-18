<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Display a listing of the documents.
     */
    public function index()
    {
        $user = Auth::user();
        
        $documents = Document::with('application')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        $documentStats = [
            'total' => Document::where('user_id', $user->id)->count(),
            'approved' => Document::where('user_id', $user->id)->where('status', 'approved')->count(),
            'pending' => Document::where('user_id', $user->id)->where('status', 'pending')->count(),
            'rejected' => Document::where('user_id', $user->id)->where('status', 'rejected')->count(),
        ];
        
        $applications = Application::where('user_id', $user->id)
            ->whereIn('status', ['draft', 'submitted'])
            ->get();
        
        return view('applicant.documents.index', compact('documents', 'documentStats', 'applications'));
    }

    /**
     * Show the form for uploading a new document.
     */
    public function create()
    {
        $user = Auth::user();
        
        $applications = Application::where('user_id', $user->id)
            ->whereIn('status', ['draft', 'submitted'])
            ->get();
        
        $documentTypes = [
            Document::TYPE_BIRTH_CERTIFICATE => 'Birth Certificate',
            Document::TYPE_FORM_IV => 'Form IV Certificate',
            Document::TYPE_FORM_VI => 'Form VI Certificate',
            Document::TYPE_DIPLOMA => 'Diploma Certificate',
            Document::TYPE_DEGREE => 'Degree Certificate',
            Document::TYPE_PASSPORT_PHOTO => 'Passport Photo (JPEG/PNG, max 2MB)',
            Document::TYPE_NATIONAL_ID => 'National ID',
            Document::TYPE_OTHER => 'Other Document',
        ];
        
        return view('applicant.documents.create', compact('applications', 'documentTypes'));
    }

    /**
     * Store a newly uploaded document.
     */
    public function store(Request $request)
    {
        $request->validate([
            'application_id' => 'nullable|exists:applications,id',
            'document_type' => 'required|string',
            'document_file' => 'required|file|max:5120', // 5MB max
        ]);
        
        $user = Auth::user();
        
        // Check if application belongs to user
        if ($request->application_id) {
            $application = Application::where('id', $request->application_id)
                ->where('user_id', $user->id)
                ->first();
                
            if (!$application) {
                return redirect()->back()
                    ->with('error', 'Invalid application selected.')
                    ->withInput();
            }
        }
        
        $file = $request->file('document_file');
        $originalName = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $mimeType = $file->getMimeType();
        
        // Generate unique filename
        $fileName = 'doc_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        
        // Store file
        $path = $file->storeAs('documents/' . $user->id, $fileName, 'public');
        
        // Create document record
        Document::create([
            'user_id' => $user->id,
            'application_id' => $request->application_id,
            'document_type' => $request->document_type,
            'original_name' => $originalName,
            'file_name' => $fileName,
            'file_path' => $path,
            'file_size' => $this->formatBytes($fileSize),
            'mime_type' => $mimeType,
            'status' => Document::STATUS_PENDING,
        ]);
        
        return redirect()->route('applicant.documents.index')
            ->with('success', 'Document uploaded successfully. It will be reviewed by the admissions team.');
    }

    /**
     * Display the specified document.
     */
    public function show(Document $document)
    {
        $this->authorize('view', $document);
        
        return view('applicant.documents.show', compact('document'));
    }

    /**
     * Download the document file.
     */
    public function download(Document $document)
    {
        $this->authorize('view', $document);
        
        if (!Storage::disk('public')->exists($document->file_path)) {
            return redirect()->back()
                ->with('error', 'Document file not found.');
        }
        
        return Storage::disk('public')->download($document->file_path, $document->original_name);
    }

    /**
     * Remove the specified document.
     */
    public function destroy(Document $document)
    {
        $this->authorize('delete', $document);
        
        // Delete file from storage
        Storage::disk('public')->delete($document->file_path);
        
        // Delete record
        $document->delete();
        
        return redirect()->route('applicant.documents.index')
            ->with('success', 'Document deleted successfully.');
    }

    /**
     * Format bytes to human readable format.
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}