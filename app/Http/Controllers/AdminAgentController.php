<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Agent;
use App\Models\User;
use App\Models\Project;
use App\Models\KnowledgeBase;
use App\Models\Intent;
use App\Models\ApiEvent;
use App\Models\AgentUsageLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminAgentController extends Controller
{
    public function index()
    {
        // Auth kontrolü
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Admin kontrolü
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Bu sayfaya erişim yetkiniz yok.');
        }

        // Sadece proje tablosunda yer alan agent'ları çek
        $agents = Agent::whereNotNull('project_id')
            ->with(['user', 'project', 'integrations', 'intents', 'apiEvents', 'usageLogs'])
            ->get();

        $users = User::all();
        $projects = Project::with('user')->get();
        $sectors = Agent::getSectors();

        // İstatistikler
        $totalAgents = $agents->count();
        $activeAgents = $agents->where('is_active', true)->count();
        $totalProjects = Project::count();
        $totalKnowledgeBase = KnowledgeBase::count();
        $totalIntents = Intent::count();
        $totalApiEvents = ApiEvent::count();

        // Maliyet istatistikleri
        $todayTotalCost = AgentUsageLog::getTodayCost();
        $monthlyTotalCost = AgentUsageLog::getMonthlyCost();
        $totalCost = AgentUsageLog::getTotalCost();
        
        // Provider bazında maliyetler
        $providerCosts = AgentUsageLog::getProviderCosts();
        
        // Agent bazında maliyetler
        $agentCosts = [];
        foreach ($agents as $agent) {
            $agentCosts[$agent->id] = [
                'today_cost' => $agent->getTodayCost(),
                'monthly_cost' => $agent->getMonthlyCost(),
                'total_cost' => $agent->getTotalCost(),
                'provider_costs' => $agent->getProviderCosts()
            ];
        }

        return view('dashboard.admin-agents-management', compact(
            'agents',
            'users',
            'projects',
            'sectors',
            'totalAgents',
            'activeAgents',
            'totalProjects',
            'totalKnowledgeBase',
            'totalIntents',
            'totalApiEvents',
            'todayTotalCost',
            'monthlyTotalCost',
            'totalCost',
            'providerCosts',
            'agentCosts'
        ));
    }

    public function show(Agent $agent)
    {
        // Auth kontrolü
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Admin kontrolü
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Bu sayfaya erişim yetkiniz yok.');
        }

        // Sadece proje ile ilişkili agent'ları göster
        if (!$agent->project_id) {
            return redirect()->route('admin.agents.index')->with('error', 'Bu agent bir proje ile ilişkili değil.');
        }

        // Eager loading ile tüm ilişkili verileri tek seferde yükle
        $agent->load([
            'user', 
            'project.knowledgeBase', 
            'integrations', 
            'intents', 
            'apiEvents',
            'usageLogs' => function($query) {
                $query->latest()->limit(20);
            }
        ]);
        
        // İstatistikler - zaten yüklenmiş verilerden hesapla
        $totalIntents = $agent->intents->count();
        $totalApiEvents = $agent->apiEvents->count();
        $totalKnowledgeBase = $agent->project->knowledgeBase->count();
        
        return view('dashboard.admin-agent-detail', compact(
            'agent', 
            'totalIntents',
            'totalApiEvents',
            'totalKnowledgeBase'
        ));
    }

    // Agent düzenleme verilerini getir
    public function getEditData(Agent $agent)
    {
        // Auth kontrolü
        if (!auth()->check()) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        }

        // Admin kontrolü
        if (auth()->user()->role !== 'admin') {
            return response()->json(['success' => false, 'error' => 'Forbidden'], 403);
        }

        // Sadece proje ile ilişkili agent'ları göster
        if (!$agent->project_id) {
            return response()->json(['success' => false, 'error' => 'Bu agent bir proje ile ilişkili değil.'], 400);
        }

        return response()->json([
            'success' => true,
            'agent' => [
                'id' => $agent->id,
                'name' => $agent->name,
                'sector' => $agent->sector,
                'description' => $agent->description,
                'user_id' => $agent->user_id,
                'project_id' => $agent->project_id,
                'is_active' => $agent->is_active
            ]
        ]);
    }

    // Gerçek zamanlı maliyet istatistikleri
    public function getCostStatistics()
    {
        // Auth kontrolü
        if (!auth()->check()) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        }

        // Admin kontrolü
        if (auth()->user()->role !== 'admin') {
            return response()->json(['success' => false, 'error' => 'Forbidden'], 403);
        }

        try {
            // Maliyet istatistikleri
            $todayTotalCost = AgentUsageLog::getTodayCost();
            $monthlyTotalCost = AgentUsageLog::getMonthlyCost();
            $totalCost = AgentUsageLog::getTotalCost();
            
            // Provider bazında maliyetler
            $providerCosts = AgentUsageLog::getProviderCosts();
            
            // Agent bazında maliyetler
            $agents = Agent::whereNotNull('project_id')->get();
            $agentCosts = [];
            foreach ($agents as $agent) {
                $agentCosts[$agent->id] = [
                    'today_cost' => $agent->getTodayCost(),
                    'monthly_cost' => $agent->getMonthlyCost(),
                    'total_cost' => $agent->getTotalCost(),
                    'provider_costs' => $agent->getProviderCosts()
                ];
            }

            return response()->json([
                'success' => true,
                'todayTotalCost' => $todayTotalCost,
                'monthlyTotalCost' => $monthlyTotalCost,
                'totalCost' => $totalCost,
                'providerCosts' => $providerCosts,
                'agentCosts' => $agentCosts,
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('Maliyet istatistikleri alınırken hata: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Maliyet istatistikleri alınırken hata oluştu'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        // Auth kontrolü
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Admin kontrolü
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Bu sayfaya erişim yetkiniz yok.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sector' => 'required|in:' . implode(',', array_keys(Agent::getSectors())),
            'description' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
            'project_id' => 'required|exists:projects,id'
        ]);

        $validated['role_name'] = 'ai_agent';
        $validated['status'] = 'active';
        $validated['usage_limit'] = 1000;
        $validated['model_id'] = null;
        
        $agent = Agent::create($validated);

        return response()->json([
            'message' => 'Agent başarıyla oluşturuldu',
            'agent' => $agent->load(['user', 'project'])
        ]);
    }

    public function update(Request $request, Agent $agent)
    {
        // Auth kontrolü
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Admin kontrolü
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Bu sayfaya erişim yetkiniz yok.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'model_settings' => 'nullable|array',
            'is_active' => 'boolean',
            'user_id' => 'required|exists:users,id',
            'project_id' => 'required|exists:projects,id'
        ]);

        $agent->update($validated);

        return response()->json([
            'message' => 'Agent başarıyla güncellendi',
            'agent' => $agent->load(['user', 'project'])
        ]);
    }

    public function destroy(Agent $agent)
    {
        // Auth kontrolü
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Admin kontrolü
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Bu sayfaya erişim yetkiniz yok.');
        }

        $agent->delete();

        return response()->json([
            'message' => 'Agent başarıyla silindi'
        ]);
    }

    public function toggleStatus(Agent $agent)
    {
        // Auth kontrolü
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Admin kontrolü
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Bu sayfaya erişim yetkiniz yok.');
        }

        $agent->update(['is_active' => !$agent->is_active]);

        return response()->json([
            'message' => 'Agent durumu güncellendi',
            'is_active' => $agent->is_active
        ]);
    }

    // Knowledge Base Yönetimi
    public function knowledgeBase(Agent $agent)
    {
        if (!$agent->project_id) {
            return redirect()->route('admin.agents.index')->with('error', 'Bu agent bir proje ile ilişkili değil.');
        }

        $knowledgeBaseItems = $agent->project->knowledgeBase()->latest()->get();
        
        return view('dashboard.admin-agent-knowledge-base', compact('agent', 'knowledgeBaseItems'));
    }

    // Intent Yönetimi
    public function intents(Agent $agent)
    {
        if (!$agent->project_id) {
            return redirect()->route('admin.agents.index')->with('error', 'Bu agent bir proje ile ilişkili değil.');
        }

        $intents = $agent->intents()->latest()->get();
        
        return view('dashboard.admin-agent-intents', compact('agent', 'intents'));
    }

    // API Events Yönetimi
    public function apiEvents(Agent $agent)
    {
        if (!$agent->project_id) {
            return redirect()->route('admin.agents.index')->with('error', 'Bu agent bir proje ile ilişkili değil.');
        }

        $apiEvents = $agent->apiEvents()->with('intent')->latest()->get();
        
        return view('dashboard.admin-agent-api-events', compact('agent', 'apiEvents'));
    }
} 