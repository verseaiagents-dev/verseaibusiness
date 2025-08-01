<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeBase;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class KnowledgeBaseController extends Controller
{
    /**
     * Get knowledge base documents for a project
     */
    public function index(Project $project)
    {
        $user = Auth::user();
        
        // Check if project belongs to user
        if ($project->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bu projeye erişim izniniz yok.'
            ], 403);
        }

        $documents = $project->knowledgeBase()->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $documents
        ]);
    }

    /**
     * Upload a document to knowledge base
     */
    public function store(Request $request, Project $project)
    {
        $user = Auth::user();
        
        // Check if project belongs to user
        if ($project->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bu projeye erişim izniniz yok.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'document' => 'required|file|mimes:pdf,doc,docx,txt|max:10240', // 10MB max
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ], [
            'document.required' => 'Belge seçimi zorunludur.',
            'document.file' => 'Geçerli bir dosya seçin.',
            'document.mimes' => 'Sadece PDF, DOC, DOCX ve TXT dosyaları kabul edilir.',
            'document.max' => 'Dosya boyutu maksimum 10MB olmalıdır.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('document');
            $fileName = $request->input('name') ?: $file->getClientOriginalName();
            $fileType = $file->getClientMimeType();
            $fileSize = $file->getSize();
            
            // Generate unique filename
            $uniqueFileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Store file in storage/app/knowledge-base directory
            $filePath = $file->storeAs('knowledge-base', $uniqueFileName, 'local');
            
            // Create knowledge base entry
            $knowledgeBase = KnowledgeBase::create([
                'project_id' => $project->id,
                'file_name' => $fileName,
                'file_path' => $filePath,
                'file_type' => $fileType,
                'file_size' => $fileSize,
                'content' => null, // Will be extracted later if needed
                'metadata' => [
                    'original_name' => $file->getClientOriginalName(),
                    'uploaded_by' => $user->id,
                    'uploaded_at' => now()->toISOString(),
                ],
                'status' => 'active',
                'ai_processing_status' => 'pending'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Belge başarıyla yüklendi.',
                'data' => $knowledgeBase
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Belge yüklenirken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import documents to knowledge base (CSV, PDF, XML, etc.)
     */
    public function import(Request $request, Project $project)
    {
        $user = Auth::user();
        
        // Check if project belongs to user
        if ($project->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bu projeye erişim izniniz yok.'
            ], 403);
        }

        // Debug: Log request data
        \Log::info('Import request data:', $request->all());

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:csv,pdf,xml,sitemap,scraping,url',
            'file' => 'required_if:type,csv,pdf,xml|file|mimes:csv,pdf,xml,txt|max:10240',
            'sitemap_url' => 'required_if:type,sitemap|url',
            'website_url' => 'required_if:type,scraping|url',
            'max_pages' => 'required_if:type,scraping|integer|min:1|max:100',
            'url' => 'required_if:type,url|url',
        ]);

        if ($validator->fails()) {
            \Log::error('Import validation failed:', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $type = $request->input('type');
            $fileName = '';
            $filePath = '';
            $fileType = '';
            $fileSize = 0;
            $content = '';

            \Log::info('Processing import type:', ['type' => $type]);

            switch ($type) {
                case 'csv':
                    $file = $request->file('file');
                    $fileName = $file->getClientOriginalName();
                    $fileType = 'text/csv';
                    $fileSize = $file->getSize();
                    $uniqueFileName = time() . '_' . uniqid() . '.csv';
                    $filePath = $file->storeAs('knowledge-base', $uniqueFileName, 'local');
                    // CSV içeriğini oku
                    $content = file_get_contents($file->getRealPath());
                    \Log::info('CSV file processed:', ['fileName' => $fileName, 'fileSize' => $fileSize]);
                    break;
                case 'pdf':
                case 'xml':
                    $file = $request->file('file');
                    $fileName = $file->getClientOriginalName();
                    $fileType = $file->getClientMimeType();
                    $fileSize = $file->getSize();
                    $uniqueFileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $filePath = $file->storeAs('knowledge-base', $uniqueFileName, 'local');
                    break;

                case 'sitemap':
                    $sitemapUrl = $request->input('sitemap_url');
                    $fileName = 'sitemap_' . time() . '.xml';
                    $content = $this->fetchSitemapContent($sitemapUrl);
                    $fileType = 'text/xml';
                    $fileSize = strlen($content);
                    break;

                case 'scraping':
                    $websiteUrl = $request->input('website_url');
                    $maxPages = $request->input('max_pages', 10);
                    $fileName = 'scraped_' . time() . '.txt';
                    $content = $this->scrapeWebsite($websiteUrl, $maxPages);
                    $fileType = 'text/plain';
                    $fileSize = strlen($content);
                    break;

                case 'url':
                    $url = $request->input('url');
                    $fileName = 'url_' . time() . '.txt';
                    $content = $this->fetchUrlContent($url);
                    $fileType = 'text/plain';
                    $fileSize = strlen($content);
                    break;
            }

            // Create knowledge base entry
            $knowledgeBase = KnowledgeBase::create([
                'project_id' => $project->id,
                'file_name' => $fileName,
                'file_path' => $filePath ?: null,
                'file_type' => $fileType,
                'file_size' => $fileSize,
                'content' => $content ?: null,
                'metadata' => [
                    'import_type' => $type,
                    'imported_by' => $user->id,
                    'imported_at' => now()->toISOString(),
                    'import_params' => $request->except(['file']),
                ],
                'status' => 'active',
                'ai_processing_status' => 'pending'
            ]);

            \Log::info('Knowledge base entry created:', ['id' => $knowledgeBase->id, 'fileName' => $fileName]);

            return response()->json([
                'success' => true,
                'message' => ucfirst($type) . ' import başarıyla tamamlandı.',
                'data' => $knowledgeBase
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Import error:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Import işlemi sırasında hata: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process knowledge base document with AI
     */
    public function processWithAi(Request $request, Project $project, KnowledgeBase $knowledgeBase)
    {
        $user = Auth::user();
        
        // Check if project belongs to user
        if ($project->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bu projeye erişim izniniz yok.'
            ], 403);
        }

        // Check if knowledge base belongs to project
        if ($knowledgeBase->project_id !== $project->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bu belgeye erişim izniniz yok.'
            ], 403);
        }

        try {
            // Update processing status
            $knowledgeBase->update([
                'ai_processing_status' => 'processing'
            ]);

            // Get content to process
            $content = $knowledgeBase->content;
            if (!$content && $knowledgeBase->file_path) {
                $content = $this->extractContentFromFile($knowledgeBase->file_path, $knowledgeBase->file_type);
            }

            if (!$content) {
                throw new \Exception('İşlenecek içerik bulunamadı.');
            }

            // Process with AI
            $aiResults = $this->processContentWithAi($content, $knowledgeBase->file_type);

            // Update knowledge base with AI results
            $knowledgeBase->update([
                'ai_processed_content' => $aiResults['processed_content'],
                'ai_summary' => $aiResults['summary'],
                'ai_categories' => $aiResults['categories'],
                'ai_embeddings' => $aiResults['embeddings'],
                'ai_processing_status' => 'completed',
                'ai_metadata' => $aiResults['metadata'],
                'ai_processed_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'AI işleme başarıyla tamamlandı.',
                'data' => $knowledgeBase
            ]);

        } catch (\Exception $e) {
            $knowledgeBase->update([
                'ai_processing_status' => 'failed'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'AI işleme sırasında hata: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific knowledge base document
     */
    public function show(Project $project, KnowledgeBase $knowledgeBase)
    {
        $user = Auth::user();
        
        // Check if project belongs to user
        if ($project->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bu projeye erişim izniniz yok.'
            ], 403);
        }

        // Check if knowledge base belongs to project
        if ($knowledgeBase->project_id !== $project->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bu belgeye erişim izniniz yok.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $knowledgeBase
        ]);
    }

    /**
     * Delete a knowledge base document
     */
    public function destroy(Project $project, KnowledgeBase $knowledgeBase)
    {
        $user = Auth::user();
        
        // Check if project belongs to user
        if ($project->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bu projeye erişim izniniz yok.'
            ], 403);
        }

        // Check if knowledge base belongs to project
        if ($knowledgeBase->project_id !== $project->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bu belgeye erişim izniniz yok.'
            ], 403);
        }

        try {
            // Delete file from storage
            if (Storage::disk('local')->exists($knowledgeBase->file_path)) {
                Storage::disk('local')->delete($knowledgeBase->file_path);
            }
            
            // Delete from database
            $knowledgeBase->delete();

            return response()->json([
                'success' => true,
                'message' => 'Belge başarıyla silindi.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Belge silinirken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download a knowledge base document
     */
    public function download(Project $project, KnowledgeBase $knowledgeBase)
    {
        $user = Auth::user();
        
        // Check if project belongs to user
        if ($project->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bu projeye erişim izniniz yok.'
            ], 403);
        }

        // Check if knowledge base belongs to project
        if ($knowledgeBase->project_id !== $project->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bu belgeye erişim izniniz yok.'
            ], 403);
        }

        try {
            // Check if file exists
            if (!Storage::disk('local')->exists($knowledgeBase->file_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dosya bulunamadı.'
                ], 404);
            }

            // Return file download
            return Storage::disk('local')->download($knowledgeBase->file_path, $knowledgeBase->file_name);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dosya indirilirken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper methods for content processing
     */
    private function fetchSitemapContent(string $url): string
    {
        $response = Http::get($url);
        return $response->body();
    }

    private function scrapeWebsite(string $url, int $maxPages): string
    {
        // Basic web scraping implementation
        $response = Http::get($url);
        $html = $response->body();
        
        // Extract text content (basic implementation)
        $text = strip_tags($html);
        $text = preg_replace('/\s+/', ' ', $text);
        
        return $text;
    }

    private function fetchUrlContent(string $url): string
    {
        $response = Http::get($url);
        $html = $response->body();
        
        // Extract text content
        $text = strip_tags($html);
        $text = preg_replace('/\s+/', ' ', $text);
        
        return $text;
    }

    private function extractContentFromFile(string $filePath, string $fileType): string
    {
        if (!Storage::disk('local')->exists($filePath)) {
            return '';
        }

        $content = Storage::disk('local')->get($filePath);

        switch ($fileType) {
            case 'application/pdf':
                // PDF text extraction (basic implementation)
                return $this->extractPdfText($content);
            case 'text/csv':
                return $content;
            case 'text/xml':
                return $content;
            default:
                return $content;
        }
    }

    private function extractPdfText(string $pdfContent): string
    {
        // Basic PDF text extraction
        // In production, you might want to use a library like Smalot\PdfParser
        return $pdfContent; // Placeholder
    }

    private function processContentWithAi(string $content, string $fileType): array
    {
        // This is a placeholder implementation
        // In production, you would integrate with ChatGPT API
        
        return [
            'processed_content' => $content,
            'summary' => 'Bu belge hakkında AI tarafından oluşturulan özet.',
            'categories' => ['genel', 'bilgi'],
            'embeddings' => [],
            'metadata' => [
                'processed_at' => now()->toISOString(),
                'file_type' => $fileType,
                'content_length' => strlen($content)
            ]
        ];
    }
}
