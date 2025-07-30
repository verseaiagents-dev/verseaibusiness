<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeBase;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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
                'status' => 'active'
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
}
