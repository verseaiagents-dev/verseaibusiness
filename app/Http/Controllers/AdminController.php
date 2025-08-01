<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AdminController extends Controller
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

        return view('dashboard.admin-panel');
    }

    public function aiSettings()
    {
        // Auth kontrolü
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Admin kontrolü
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Bu sayfaya erişim yetkiniz yok.');
        }

        $agents = \App\Models\Agent::with(['integrations', 'intents'])->get();
        $sectors = \App\Models\Agent::getSectors();
        $integrationTypes = \App\Models\AgentIntegration::getIntegrationTypes();
        
        return view('dashboard.ai-settings', compact('agents', 'sectors', 'integrationTypes'));
    }

    public function storeAgent(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sector' => 'required|in:' . implode(',', array_keys(\App\Models\Agent::getSectors())),
            'description' => 'nullable|string'
        ]);

        $validated['user_id'] = auth()->id();
        $validated['role_name'] = 'ai_agent';
        $validated['status'] = 'active';
        $validated['usage_limit'] = 1000;
        $validated['model_id'] = null;
        $agent = \App\Models\Agent::create($validated);

        return response()->json([
            'message' => 'Agent başarıyla oluşturuldu',
            'agent' => $agent
        ]);
    }

    public function updateAgent(Request $request, \App\Models\Agent $agent)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'model_settings' => 'nullable|array',
            'is_active' => 'boolean'
        ]);

        $agent->update($validated);

        return response()->json([
            'message' => 'Agent başarıyla güncellendi',
            'agent' => $agent
        ]);
    }



    public function userManagement(Request $request)
    {
        // Auth kontrolü
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Admin kontrolü
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Bu sayfaya erişim yetkiniz yok.');
        }

        // Kullanıcıları çek
        $query = User::query();

        // Arama filtresi
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Rol filtresi
        if ($request->has('role') && $request->get('role') !== '') {
            $query->where('role', $request->get('role'));
        }

        // Durum filtresi
        if ($request->has('status') && $request->get('status') !== '') {
            $status = $request->get('status') === 'active' ? 1 : 0;
            $query->where('status', $status);
        }

        // Sıralama
        $query->orderBy('created_at', 'desc');

        // Sayfalama
        $users = $query->paginate(10);

        return view('dashboard.user-management', compact('users'));
    }

    public function analytics()
    {
        // Auth kontrolü
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Admin kontrolü
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Bu sayfaya erişim yetkiniz yok.');
        }

        return view('dashboard.analytics');
    }

    public function adminSettings()
    {
        // Auth kontrolü
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Admin kontrolü
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Bu sayfaya erişim yetkiniz yok.');
        }

        return view('dashboard.admin-settings');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function editUser(User $user)
    {
        return view('dashboard.users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role' => ['required', 'string', 'in:admin,user'],
            'status' => ['required', 'boolean'],
        ]);

        $user->update($validated);

        return redirect()->route('admin.user-management')
            ->with('success', __('admin.user_updated_successfully'));
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroyUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.user-management')
                ->with('error', __('admin.cannot_delete_self'));
        }

        $user->delete();

        return redirect()->route('admin.user-management')
            ->with('success', __('admin.user_deleted_successfully'));
    }
} 