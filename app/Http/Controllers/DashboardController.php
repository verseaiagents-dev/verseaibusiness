<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller

{


public function showdashboard(){
    $user = Auth::user();
    
    // Get user statistics
    $stats = [
        'token_balance' => $user->token_balance,
        'active_agents' => $user->agents()->count(),
        'total_agents' => $user->agents()->count(),
        'plan_type' => $user->plan_type ?? 'Free',
    ];

    // Get recent agents (if any)
    $recent_agents = $user->agents()
        ->latest()
        ->take(5)
        ->get();

    // Get user activity data
    $activities = [
        [
            'type' => 'login',
            'title' => 'Başarılı Giriş',
            'description' => 'Hesabınıza başarıyla giriş yaptınız',
            'icon' => 'mdi-login',
            'color' => 'green',
            'time' => now()->format('H:i')
        ]
    ];

    // Add agent activity if user has agents
    if ($user->agents()->count() > 0) {
        $activities[] = [
            'type' => 'agent',
            'title' => 'Agent Aktif',
            'description' => $user->agents()->count() . ' agent\'ınız var',
            'icon' => 'mdi-robot',
            'color' => 'blue',
            'time' => now()->format('H:i')
        ];
    } else {
        $activities[] = [
            'type' => 'create_agent',
            'title' => 'İlk Agent\'ınızı Oluşturun',
            'description' => 'AI agent oluşturarak başlayın',
            'icon' => 'mdi-plus',
            'color' => 'amber',
            'time' => now()->format('H:i')
        ];
    }

    return view('dashboard.dashboard', compact('stats', 'recent_agents', 'activities'));
}

    /**
     * Show the dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        $user = Auth::user();
        
        // Get user statistics
        $stats = [
            'token_balance' => $user->token_balance,
            'active_agents' => $user->agents()->count(), // All agents are considered active now
            'total_agents' => $user->agents()->count(),
            'plan_type' => $user->plan_type ?? 'Free',
        ];

        // Get recent agents (if any)
        $recent_agents = $user->agents()
            ->latest()
            ->take(5)
            ->get();

        // Get user activity data
        $activities = [
            [
                'type' => 'login',
                'title' => 'Başarılı Giriş',
                'description' => 'Hesabınıza başarıyla giriş yaptınız',
                'icon' => 'mdi-login',
                'color' => 'green',
                'time' => now()->format('H:i')
            ]
        ];

        // Add agent activity if user has agents
        if ($user->agents()->count() > 0) {
            $activities[] = [
                'type' => 'agent',
                'title' => 'Agent Aktif',
                'description' => $user->agents()->count() . ' agent\'ınız var',
                'icon' => 'mdi-robot',
                'color' => 'blue',
                'time' => now()->format('H:i')
            ];
        } else {
            $activities[] = [
                'type' => 'create_agent',
                'title' => 'İlk Agent\'ınızı Oluşturun',
                'description' => 'AI agent oluşturarak başlayın',
                'icon' => 'mdi-plus',
                'color' => 'amber',
                'time' => now()->format('H:i')
            ];
        }

        return view('dashboard.dashboard', compact('stats', 'recent_agents', 'activities'));
    }

    /**
     * Get dashboard statistics for AJAX requests.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStats()
    {
        $user = Auth::user();
        
        $stats = [
            'token_balance' => $user->token_balance,
            'active_agents' => $user->agents()->count(),
            'total_agents' => $user->agents()->count(),
            'plan_type' => $user->plan_type ?? 'Free',
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get recent activities for AJAX requests.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActivities()
    {
        $user = Auth::user();
        
        $activities = [
            [
                'type' => 'login',
                'title' => 'Başarılı Giriş',
                'description' => 'Hesabınıza başarıyla giriş yaptınız',
                'icon' => 'mdi-login',
                'color' => 'green',
                'time' => now()->format('H:i')
            ]
        ];

        if ($user->agents()->count() > 0) {
            $activities[] = [
                'type' => 'agent',
                'title' => 'Agent Aktif',
                'description' => $user->agents()->count() . ' agent\'ınız var',
                'icon' => 'mdi-robot',
                'color' => 'blue',
                'time' => now()->format('H:i')
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $activities
        ]);
    }

    /**
     * Show knowledge base for a specific project.
     *
     * @param \App\Models\Project $project
     * @return \Illuminate\View\View
     */
    public function knowledgeBase($project)
    {
        $user = Auth::user();
        
        // Check if project belongs to user
        $project = $user->projects()->findOrFail($project);
        
        // Get knowledge base documents
        $documents = $project->knowledgeBase()->latest()->get();
        $totalDocuments = $documents->count();
        $lastUpdated = $documents->first() ? $documents->first()->updated_at : null;
        
        $knowledgeBaseData = [
            'project' => $project,
            'documents' => $documents,
            'total_documents' => $totalDocuments,
            'last_updated' => $lastUpdated
        ];

        return view('dashboard.knowledge-base', compact('knowledgeBaseData'));
    }
}
