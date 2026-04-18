<?php

namespace App\Http\Controllers\Admission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class SupportController extends Controller
{
    /**
     * Display help center
     */
    public function helpCenter(Request $request)
    {
        $search = $request->get('search');
        $category = $request->get('category');
        
        // Get help articles
        $query = DB::table('help_articles')
            ->where('is_published', 1)
            ->orderBy('views', 'desc')
            ->orderBy('created_at', 'desc');
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('tags', 'like', "%{$search}%");
            });
        }
        
        if ($category) {
            $query->where('category', $category);
        }
        
        $articles = $query->paginate(10);
        
        // Get popular articles (most viewed)
        $popularArticles = DB::table('help_articles')
            ->where('is_published', 1)
            ->orderBy('views', 'desc')
            ->limit(5)
            ->get();
        
        // Get categories with article counts
        $categories = DB::table('help_articles')
            ->select('category', DB::raw('count(*) as total'))
            ->where('is_published', 1)
            ->groupBy('category')
            ->get();
        
        // Get recent articles
        $recentArticles = DB::table('help_articles')
            ->where('is_published', 1)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        return view('admission.support.help-center', compact(
            'articles', 'popularArticles', 'categories', 'recentArticles', 'search', 'category'
        ));
    }
    
    /**
     * Show single help article
     */
    public function showArticle($id)
    {
        // Increment view count
        DB::table('help_articles')
            ->where('id', $id)
            ->increment('views');
        
        $article = DB::table('help_articles')
            ->where('id', $id)
            ->where('is_published', 1)
            ->first();
        
        if (!$article) {
            abort(404, 'Article not found');
        }
        
        // Get related articles (same category)
        $relatedArticles = DB::table('help_articles')
            ->where('category', $article->category)
            ->where('id', '!=', $id)
            ->where('is_published', 1)
            ->limit(5)
            ->get();
        
        return view('admission.support.article', compact('article', 'relatedArticles'));
    }
    
    /**
     * Display FAQ page
     */
    public function faq(Request $request)
    {
        $category = $request->get('category');
        $search = $request->get('search');
        
        $query = DB::table('faqs')
            ->where('is_published', 1)
            ->orderBy('order_position', 'asc');
        
        if ($category) {
            $query->where('category', $category);
        }
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                  ->orWhere('answer', 'like', "%{$search}%");
            });
        }
        
        $faqs = $query->get();
        
        // Group FAQs by category
        $faqsByCategory = [];
        foreach ($faqs as $faq) {
            if (!isset($faqsByCategory[$faq->category])) {
                $faqsByCategory[$faq->category] = [];
            }
            $faqsByCategory[$faq->category][] = $faq;
        }
        
        // Get all categories
        $categories = DB::table('faqs')
            ->select('category')
            ->where('is_published', 1)
            ->distinct()
            ->pluck('category');
        
        return view('admission.support.faq', compact('faqsByCategory', 'categories', 'category', 'search'));
    }
    
    /**
     * Submit support ticket
     */
    public function submitTicket(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'category' => 'required|in:technical,bug,feature_request,account,payment,other',
        ]);
        
        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        try {
            $ticketNumber = $this->generateTicketNumber();
            
            $ticketId = DB::table('support_tickets')->insertGetId([
                'ticket_number' => $ticketNumber,
                'user_id' => auth()->id(),
                'subject' => $request->subject,
                'message' => $request->message,
                'priority' => $request->priority,
                'category' => $request->category,
                'status' => 'open',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            Log::info('Support ticket created', ['ticket_id' => $ticketId, 'ticket_number' => $ticketNumber]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ticket submitted successfully',
                    'ticket_number' => $ticketNumber
                ]);
            }
            
            return redirect()->back()->with('success', "Ticket #{$ticketNumber} submitted successfully");
            
        } catch (\Exception $e) {
            Log::error('Failed to submit ticket: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to submit ticket'], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to submit ticket: ' . $e->getMessage());
        }
    }
    
    /**
     * Generate ticket number
     */
    private function generateTicketNumber()
    {
        $year = date('Y');
        $month = date('m');
        $count = DB::table('support_tickets')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count() + 1;
        $sequence = str_pad($count, 4, '0', STR_PAD_LEFT);
        return "TKT/{$year}/{$month}/{$sequence}";
    }
    
    /**
     * Get system status (AJAX)
     */
    public function systemStatus()
    {
        $status = [
            'database' => $this->checkDatabaseConnection(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'last_backup' => $this->getLastBackupDate(),
            'server_load' => $this->getServerLoad(),
        ];
        
        return response()->json($status);
    }
    
    /**
     * Check database connection
     */
    private function checkDatabaseConnection()
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'healthy', 'message' => 'Connected'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Check cache
     */
    private function checkCache()
    {
        try {
            Cache::put('health_check', true, 1);
            $result = Cache::get('health_check');
            Cache::forget('health_check');
            return ['status' => 'healthy', 'message' => 'Cache working'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Check storage
     */
    private function checkStorage()
    {
        try {
            $freeSpace = disk_free_space('/');
            $totalSpace = disk_total_space('/');
            $usedPercentage = round((($totalSpace - $freeSpace) / $totalSpace) * 100, 2);
            
            return [
                'status' => $usedPercentage > 90 ? 'warning' : 'healthy',
                'free' => $this->formatBytes($freeSpace),
                'total' => $this->formatBytes($totalSpace),
                'used_percentage' => $usedPercentage
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Get last backup date
     */
    private function getLastBackupDate()
    {
        $backup = DB::table('backups')
            ->orderBy('created_at', 'desc')
            ->first();
        
        if ($backup) {
            return [
                'date' => Carbon::parse($backup->created_at)->format('d/m/Y H:i:s'),
                'size' => $this->formatBytes($backup->size ?? 0)
            ];
        }
        
        return ['date' => 'No backup found', 'size' => 'N/A'];
    }
    
    /**
     * Get server load
     */
    private function getServerLoad()
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return [
                '1min' => round($load[0], 2),
                '5min' => round($load[1], 2),
                '15min' => round($load[2], 2)
            ];
        }
        
        return ['1min' => 'N/A', '5min' => 'N/A', '15min' => 'N/A'];
    }
    
    /**
     * Format bytes
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