<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    /**
     * Get all projects for the authenticated user
     */
    public function index()
    {
        $user = Auth::user();
        $projects = $user->projects()->with('knowledgeBase')->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $projects
        ]);
    }

    /**
     * Create a new project
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'token_limit' => 'required|integer|min:1',
        ], [
            'name.required' => 'Proje ismi zorunludur.',
            'token_limit.required' => 'Token limit zorunludur.',
            'token_limit.min' => 'Token limit en az 1 olmalıdır.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if user has enough token balance
        if ($user->token_balance < $request->token_limit) {
            return response()->json([
                'success' => false,
                'message' => 'Yetersiz token bakiyesi. Mevcut bakiye: ' . $user->token_balance . ', Gereken: ' . $request->token_limit
            ], 400);
        }

        try {
            $project = Project::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'description' => $request->description,
                'token_limit' => $request->token_limit,
            ]);

            // Deduct tokens from user balance
            $user->decrement('token_balance', $request->token_limit);

            return response()->json([
                'success' => true,
                'message' => 'Proje başarıyla oluşturuldu.',
                'data' => $project,
                'remaining_balance' => $user->fresh()->token_balance
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Proje oluşturulurken bir hata oluştu.'
            ], 500);
        }
    }

    /**
     * Get a specific project
     */
    public function show(Project $project)
    {
        $user = Auth::user();
        
        if ($project->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bu projeye erişim izniniz yok.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $project->load('knowledgeBase')
        ]);
    }

    /**
     * Update a project
     */
    public function update(Request $request, Project $project)
    {
        $user = Auth::user();
        
        if ($project->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bu projeye erişim izniniz yok.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'token_limit' => 'sometimes|required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // If token_limit is being updated, check balance
        if ($request->has('token_limit') && $request->token_limit > $project->token_limit) {
            $additionalTokens = $request->token_limit - $project->token_limit;
            if ($user->token_balance < $additionalTokens) {
                return response()->json([
                    'success' => false,
                    'message' => 'Yetersiz token bakiyesi. Gereken ek bakiye: ' . $additionalTokens
                ], 400);
            }
        }

        try {
            $oldTokenLimit = $project->token_limit;
            
            $project->update($request->only([
                'name', 'description', 'token_limit'
            ]));

            // Adjust user token balance if token_limit changed
            if ($request->has('token_limit') && $request->token_limit !== $oldTokenLimit) {
                $difference = $request->token_limit - $oldTokenLimit;
                $user->increment('token_balance', -$difference);
            }

            return response()->json([
                'success' => true,
                'message' => 'Proje başarıyla güncellendi.',
                'data' => $project->fresh(),
                'remaining_balance' => $user->fresh()->token_balance
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Proje güncellenirken bir hata oluştu.'
            ], 500);
        }
    }

    /**
     * Delete a project
     */
    public function destroy(Project $project)
    {
        $user = Auth::user();
        
        if ($project->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bu projeye erişim izniniz yok.'
            ], 403);
        }

        try {
            // Return tokens to user balance
            $user->increment('token_balance', $project->token_limit);
            
            $project->delete();

            return response()->json([
                'success' => true,
                'message' => 'Proje başarıyla silindi.',
                'remaining_balance' => $user->fresh()->token_balance
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Proje silinirken bir hata oluştu.'
            ], 500);
        }
    }

    /**
     * Get user's token balance and available models
     */
    public function getUserInfo()
    {
        $user = Auth::user();
        
        return response()->json([
            'success' => true,
            'data' => [
                'token_balance' => $user->token_balance,
                'available_models' => [
                    'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
                    'gpt-4' => 'GPT-4',
                    'claude-3-sonnet' => 'Claude 3 Sonnet',
                    'claude-3-opus' => 'Claude 3 Opus'
                ]
            ]
        ]);
    }
}
