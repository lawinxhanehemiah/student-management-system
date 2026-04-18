<?php

namespace App\Http\Controllers\Admission;

use App\Http\Controllers\Controller;
use App\Services\NectaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NectaController extends Controller
{
    protected $nectaService;
    
    public function __construct(NectaService $nectaService)
    {
        $this->nectaService = $nectaService;
    }
    
    public function fetchCseeResults(Request $request)
    {
        try {
            $request->validate([
                'index_number' => 'required|string|max:30',
                'year' => 'nullable|integer|min:2010|max:' . date('Y')
            ]);
            
            $results = $this->nectaService->fetchCseeResults(
                $request->index_number, 
                $request->year
            );
            
            Log::info('NECTA fetch result', ['success' => $results['success'], 'source' => $results['source'] ?? 'unknown']);
            
            return response()->json($results);
            
        } catch (\Exception $e) {
            Log::error('NECTA fetch error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}